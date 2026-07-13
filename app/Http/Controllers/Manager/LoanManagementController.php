<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\LoanAmortizationSchedule;
use App\Models\Party;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanManagementController extends Controller
{
    public function index(Request $request)
    {
        $direction = $request->get('direction', 'taken'); // taken or given

        $stats = [
            'total_active' => Loan::where('direction', $direction)->where('status', 'active')->count(),
            'total_outstanding' => Loan::where('direction', $direction)->whereIn('status', ['active', 'partial_paid'])->get()->sum('balance_amount'),
            'total_overdue' => Loan::where('direction', $direction)->where('status', 'overdue')->count(),
            'total_closed' => Loan::where('direction', $direction)->where('status', 'closed')->count(),
        ];

        $loans = Loan::with(['party', 'company'])
            ->where('direction', $direction)
            ->when($request->status, function($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->party_id, function($q) use ($request) {
                return $q->where('party_id', $request->party_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $parties = Party::orderBy('name')->get();

        return view('Manager.loan_management.index', compact('loans', 'stats', 'direction', 'parties'));
    }

    public function create(Request $request)
    {
        $direction = $request->get('direction', 'taken');
        $parties = Party::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        
        return view('Manager.loan_management.create', compact('direction', 'parties', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'party_id' => 'required|exists:parties,id',
            'direction' => 'required|in:taken,given',
            'principal_amount' => 'required|numeric|min:0',
            'loan_date' => 'required|date',
            // Add more specific validations as needed
        ]);

        try {
            DB::beginTransaction();

            $loanNumberPrefix = $request->direction === 'taken' ? 'LT-' : 'LG-';
            $year = Carbon::parse($request->loan_date)->format('Y');
            $lastLoan = Loan::where('direction', $request->direction)
                ->whereYear('loan_date', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastLoan ? intval(substr($lastLoan->loan_number, -4)) + 1 : 1;
            $loanNumber = $loanNumberPrefix . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $loan = Loan::create(array_merge($request->except(['_token']), [
                'loan_number' => $loanNumber,
                'created_by' => auth()->id(),
                'status' => 'active',
                'interest_applicable' => $request->has('interest_applicable'),
                'enable_emi' => $request->has('enable_emi'),
                'gst_applicable' => $request->has('gst_applicable'),
                'tds_applicable' => $request->has('tds_applicable'),
                'collateral_applicable' => $request->has('collateral_applicable'),
            ]));

            // If loan is disbursed immediately
            if ($request->disbursement_date) {
                LoanTransaction::create([
                    'loan_id' => $loan->id,
                    'type' => 'disbursement',
                    'transaction_date' => $request->disbursement_date,
                    'total_amount' => $loan->principal_amount,
                    'created_by' => auth()->id(),
                    'notes' => 'Initial Disbursement',
                ]);
            }

            // Generate EMI schedule if enabled
            if ($loan->enable_emi && $loan->emi_amount && $loan->emi_count && $loan->emi_start_date) {
                $this->generateEmiSchedule($loan);
            }

            DB::commit();
            return redirect()->route('manager.loan-management.show', $loan->id)->with('success', 'Loan created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating loan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $loan = Loan::with(['party', 'company', 'transactions' => function($q) {
            $q->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
        }, 'documents', 'amortizationSchedules' => function($q) {
            $q->orderBy('installment_number', 'asc');
        }])->findOrFail($id);
        
        return view('Manager.loan_management.show', compact('loan'));
    }

    public function recordPayment(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        
        $request->validate([
            'transaction_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0.01',
            'principal_component' => 'required|numeric|min:0',
            'interest_component' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            LoanTransaction::create([
                'loan_id' => $loan->id,
                'type' => 'payment',
                'transaction_date' => $request->transaction_date,
                'total_amount' => $request->total_amount,
                'principal_component' => $request->principal_component,
                'interest_component' => $request->interest_component,
                'penalty_component' => $request->penalty_component ?? 0,
                'gst_component' => $request->gst_component ?? 0,
                'tds_component' => $request->tds_component ?? 0,
                'payment_mode' => $request->payment_mode,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // If EMI, update schedule status
            if ($loan->enable_emi && $request->has('schedule_id')) {
                $schedule = LoanAmortizationSchedule::find($request->schedule_id);
                if ($schedule && $schedule->loan_id == $loan->id) {
                    $schedule->update(['status' => 'paid']);
                }
            }

            // Update loan status if fully paid
            if ($loan->balance_amount <= 0) {
                $loan->update(['status' => 'closed']);
            } elseif ($loan->outstanding_principal < $loan->principal_amount) {
                $loan->update(['status' => 'partial_paid']);
            }

            DB::commit();
            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }
    
    public function closeLoan(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        
        try {
            $loan->update(['status' => 'closed']);
            return back()->with('success', 'Loan marked as closed.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error closing loan: ' . $e->getMessage());
        }
    }

    private function generateEmiSchedule(Loan $loan)
    {
        $date = Carbon::parse($loan->emi_start_date);
        
        // Simple fixed EMI calculation (could be more complex based on interest type reducing/flat)
        $remainingBalance = $loan->principal_amount;
        
        for ($i = 1; $i <= $loan->emi_count; $i++) {
            
            // This is a simplified split for reducing balance or flat. 
            // Real amortization logic requires financial formulas depending on interest_type.
            $principal = $loan->emi_amount - ($remainingBalance * ($loan->interest_rate / 100 / 12)); 
            if ($loan->interest_type == 'flat' || !$loan->interest_applicable) {
                $principal = $loan->principal_amount / $loan->emi_count;
            }
            $interest = max(0, $loan->emi_amount - $principal);
            $remainingBalance = max(0, $remainingBalance - $principal);

            LoanAmortizationSchedule::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'due_date' => $date->copy(),
                'emi_amount' => $loan->emi_amount,
                'principal_component' => $principal,
                'interest_component' => $interest,
                'remaining_balance' => $remainingBalance,
                'status' => 'pending',
            ]);

            $date->addMonth();
        }
    }
}
