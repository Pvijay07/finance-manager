<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Company;
use App\Models\ExpenseGenerationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StandardExpensesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get the companies the manager has access to
        $managedCompanyIds = [];
        if ($user) {
            if ($user->company_id) {
                $managedCompanyIds[] = $user->company_id;
            }
            if (method_exists($user, 'companies')) {
                $managedCompanyIds = array_merge($managedCompanyIds, $user->companies()->pluck('companies.id')->toArray());
            }
            $managedCompanyIds = array_unique(array_filter($managedCompanyIds));
        }

        // Get filters
        $companyId = $request->get('company');
        $month = $request->get('month');
        $category = $request->get('category');
        $status = $request->get('status');
        
        // Query standard expenses — include company access filter
        $query = Expense::where('source', 'standard')
            ->with(['company', 'expenseType', 'categoryRelation', 'creator', 'paidByUser'])
            ->orderBy('due_date', 'asc');

        // Restrict to manager's companies (if not admin)
        if (!empty($managedCompanyIds)) {
            $query->whereIn('company_id', $managedCompanyIds);
        }
        
        // Apply filters
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        if ($month) {
            $query->where('month_year', $month);
        }
        
        if ($category) {
            $query->where(function ($q) use ($category) {
                $q->whereHas('expenseType', function ($sq) use ($category) {
                    $sq->where('category', $category);
                })->orWhere('category_id', $category);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $expenses = $query->get();
        
        // Get companies accessible to this manager for filters
        $companiesQuery = Company::where('status', 'active');
        if (!empty($managedCompanyIds)) {
            $companiesQuery->whereIn('id', $managedCompanyIds);
        }
        $companies = $companiesQuery->get();

        $categories = ExpenseType::select('category')->distinct()->get();
        $expenseTypes = ExpenseType::where('status', 'active')->get();
        $latestLog = ExpenseGenerationLog::latest('run_date')->first();
        
        return view('Manager.standard_expenses', compact(
            'expenses', 
            'companies', 
            'categories',
            'expenseTypes',
            'latestLog'
        ));
    }

    public function generateExpenses(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('expenses:generate-standard');
            $output = \Illuminate\Support\Facades\Artisan::output();

            $latestLog = ExpenseGenerationLog::latest('run_date')->first();
            $count = $latestLog ? $latestLog->total_generated : 0;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Standard expenses generated successfully.',
                    'count'   => $count,
                    'output'  => $output
                ]);
            }

            return redirect()->back()->with('success', "Standard expenses generated successfully ({$count} generated).");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generation failed: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Generation failed: ' . $e->getMessage());
        }
    }
    
    public function updateAmount(Request $request, $id)
    {
        $request->validate([
            'actual_amount' => 'required|numeric|min:0',
            'paid_date' => 'nullable|date'
        ]);
        
        $expense = Expense::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $actualAmount = $request->actual_amount;
            $plannedAmount = $expense->planned_amount;
            $balance = $plannedAmount - $actualAmount;
            
            $updateData = [
                'actual_amount' => $actualAmount,
                'balance_amount' => $balance,
                'updated_at' => now()
            ];
            
            // If paid in full
            if ($actualAmount >= $plannedAmount) {
                $updateData['status'] = 'paid';
                $updateData['paid_date'] = $request->paid_date ?? now()->format('Y-m-d');
            } 
            // If partially paid
            elseif ($actualAmount > 0) {
                $updateData['status'] = 'pending';
            }
            
            $expense->update($updateData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Amount updated successfully!',
                'balance' => $balance
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function markAsPaid($id)
    {
        $expense = Expense::findOrFail($id);
        
        $expense->update([
            'actual_amount' => $expense->planned_amount,
            'balance_amount' => 0,
            'status' => 'paid',
            'paid_date' => now()->format('Y-m-d'),
            'paid_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Marked as paid successfully!'
        ]);
    }
    
    public function settleBalance($id)
    {
        $expense = Expense::findOrFail($id);
        
        $expense->update([
            'actual_amount' => $expense->planned_amount,
            'balance_amount' => 0,
            'status' => 'paid',
            'paid_date' => now()->format('Y-m-d'),
            'paid_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Balance settled successfully!'
        ]);
    }
    
    public function keepBalance($id)
    {
        $expense = Expense::findOrFail($id);
        
        // Create a new expense for next month with the balance
        if ($expense->balance_amount > 0) {
            Expense::create([
                'company_id' => $expense->company_id,
                'expense_type_id' => $expense->expense_type_id,
                'name' => $expense->name . ' (Balance)',
                'type' => 'standard',
                'planned_amount' => $expense->balance_amount,
                'actual_amount' => null,
                'due_date' => Carbon::parse($expense->due_date)->addMonth(),
                'status' => 'upcoming',
                'category' => $expense->category,
                'source' => 'manual',
                'month_year' => Carbon::parse($expense->due_date)->addMonth()->format('Y-m'),
                'balance_amount' => $expense->balance_amount,
                'created_by' => auth()->id()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Balance carried forward to next month!'
        ]);
    }
     public function viewLogs(Request $request)
    {
        $logs = ExpenseGenerationLog::orderBy('run_date', 'desc')
            ->paginate(20);
            
        return view('Admin.expense_generation_logs', compact('logs'));
    }
    
}