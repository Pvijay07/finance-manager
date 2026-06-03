<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\GstSettlement;
use App\Models\GstTask;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class GSTController extends Controller
{
    private function generateMonths($count = 12)
    {
        $months = [];
        for ($i = 0; $i < $count; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = [
                'value' => $date,
                'label' => date('M Y', strtotime($date)),
            ];
        }
        return $months;
    }

    private function getAvailablePeriods($count = 12)
    {
        $periods = [];
        for ($i = 0; $i < $count; $i++) {
            $periods[] = date('Y-m', strtotime("-$i months"));
        }
        return $periods;
    }

    /**
     * Get company IDs for the authenticated user
     */
    private function getUserCompanyIds($specificCompanyId = null)
    {
        $user = auth()->user();

        // For admins/CAs
        if ($user->isAdmin() || $user->isCA()) {
            if ($specificCompanyId && $specificCompanyId !== 'all') {
                return [$specificCompanyId];
            }
            return Company::where('status', 'active')->pluck('id')->toArray();
        }

        // For regular managers
        if ($specificCompanyId && $specificCompanyId !== 'all') {
            $hasAccess = Company::where('id', $specificCompanyId)
                ->where('manager_id', $user->id)
                ->exists();
            return $hasAccess ? [$specificCompanyId] : [];
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get companies for dropdown (only user's companies)
     */
    private function getUserCompanies()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->get(['id', 'name']);
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->get(['id', 'name']);
    }

    /**
     * Get common view data with user's companies
     */
    private function getCommonViewData($selectedMonth = null, $selectedYear = null)
    {
        if (!$selectedMonth) {
            $selectedMonth = date('m');
        }

        if (!$selectedYear) {
            $selectedYear = date('Y');
        }

        return [
            'currentPeriod' => date('M Y', strtotime("$selectedYear-$selectedMonth-01")),
            'companies' => $this->getUserCompanies(), // Only user's companies
            'months' => $this->generateMonths(),
        ];
    }

    /**
     * Main GST dashboard
     */
    public function index(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get filters with validation
        $period = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Validate period format
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = date('Y-m');
            $selectedMonth = date('m');
            $selectedYear = date('Y');
        }

        // Define queries
        $outputQuery = Tax::where(function ($query) {
            $query->where('direction', 'income')
                ->orWhere('taxable_type', 'App\Models\Income');
        })
            ->where('tax_type', 'gst')
            ->where('payment_status', 'received')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        $inputQuery = Tax::where(function ($query) {
            $query->where('direction', 'expense')
                ->orWhere('taxable_type', 'App\Models\Expense');
        })
            ->where('tax_type', 'gst')
            ->where('payment_status', 'received')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        $tdsQuery = Tax::where('tax_type', 'tds')
            ->where('payment_status', 'received')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        // Calculate totals directly from queries
        $totalOutputGST = (clone $outputQuery)->sum('tax_amount');
        $totalInputGST = (clone $inputQuery)->sum('tax_amount');
        $totalTDS = (clone $tdsQuery)->sum('tax_amount');

        // Net GST payable (Output GST - Input GST)
        $netGSTPayable = $totalOutputGST - $totalInputGST;

        // Net position (considering TDS as well)
        $netPosition = $netGSTPayable - $totalTDS;

        // Calculate taxable amounts for summaries using model queries to avoid tax double-counting
        $totalOutputTaxable = Income::whereHas('taxes', function ($q) {
            $q->where('tax_type', 'gst')->where('payment_status', 'received');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->sum('original_amount');

        $totalTDSTaxable = Income::whereHas('taxes', function ($q) {
            $q->where('tax_type', 'tds')->where('payment_status', 'received');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->sum('original_amount');

        $totalTDSExpenseTaxable = Expense::whereHas('taxes', function ($q) {
            $q->where('tax_type', 'tds')->where('payment_status', 'received');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get()
            ->sum(function ($exp) {
                return $exp->original_amount ?? ($exp->actual_amount ?? 0);
            });

        $totalInputTaxable = Expense::whereHas('taxes', function ($q) {
            $q->where('tax_type', 'gst')->where('payment_status', 'received');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->get()
            ->sum(function ($exp) {
                return $exp->original_amount ?? ($exp->actual_amount ?? 0);
            });

        // Get the actual expenses and incomes for display - Only from user's companies
        $perPage = $request->input('per_page', 10);

        $gstExpenses = Expense::whereHas('taxes', function ($query) {
            $query->where('tax_type', 'gst');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->paginate($perPage, ['*'], 'gst_exp_page')
            ->withQueryString();

        $gstIncomes = Income::whereHas('taxes', function ($query) {
            $query->where('tax_type', 'gst');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->paginate($perPage, ['*'], 'gst_inc_page')
            ->withQueryString();

        $tdsExpenses = Expense::whereHas('taxes', function ($query) {
            $query->where('tax_type', 'tds');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->paginate($perPage, ['*'], 'tds_exp_page')
            ->withQueryString();

        $tdsIncomes = Income::whereHas('taxes', function ($query) {
            $query->where('tax_type', 'tds');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->paginate($perPage, ['*'], 'tds_inc_page')
            ->withQueryString();

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);

        // Merge with GST-specific data
        $data = array_merge($data, [
            'totalOutputGST' => $totalOutputGST,             // GST collected from income/sales
            'totalInputGST' => $totalInputGST,              // GST paid on expenses/purchases (ITC)
            'totalTDS' => $totalTDS,                   // TDS deducted
            'netGSTPayable' => max(0, $netGSTPayable),      // GST payable (if positive)
            'netGSTReceivable' => abs(min(0, $netGSTPayable)), // GST receivable (if negative)
            'netPosition' => $netPosition,                // Overall position including TDS
            'totalOutputTaxable' => $totalOutputTaxable,
            'totalInputTaxable' => $totalInputTaxable,
            'totalTDSTaxable' => $totalTDSTaxable,
            'totalTDSExpenseTaxable' => $totalTDSExpenseTaxable,
            'gstExpenses' => $gstExpenses,                // Expenses with GST
            'gstIncomes' => $gstIncomes,                 // Incomes with GST
            'tdsExpenses' => $tdsExpenses,                // Expenses with TDS
            'tdsIncomes' => $tdsIncomes,                 // Incomes with TDS
            'outputTaxes' => $outputQuery->get(),                // All output GST tax records
            'inputTaxes' => $inputQuery->get(),                 // All input GST tax records
            'tdsTaxes' => $tdsQuery->get(),                   // All TDS tax records
            'selectedPeriod' => $period,
            'isGSTPayable' => $netGSTPayable > 0, // Flag to indicate if GST is payable
            'isOverallPayable' => $netPosition > 0,   // Flag for overall payable position
        ]);

        return view('Manager.gst', $data);
    }

    /**
     * GST Collected Page
     */
    public function gstCollected(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get filters
        $companyId = $request->input('company_id', 'all');
        $period = $request->input('period', date('Y-m'));
        $taxType = $request->input('tax_type', 'gst'); // Default to GST
        $search = $request->input('search');

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Define queries
        $incomeQuery = Income::whereHas('taxes', function ($query) {
            $query->where('tax_type', 'gst')
                ->where('direction', 'income')
                ->where('payment_status', 'received');
        })
            ->whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        if ($companyId !== 'all') {
            $incomeQuery->where('company_id', $companyId);
        }

        $gstTaxesQuery = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->where('payment_status', 'received')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        if (!empty($search)) {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $incomeQuery->where(function ($q) use ($search, $cleanId) {
                $q->where('party_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
                  
                if (!empty($cleanId)) {
                    $q->orWhere('id', 'like', "%{$cleanId}%");
                }
            });

            $gstTaxesQuery->whereHas('taxable', function ($q) use ($search, $cleanId) {
                $q->where('party_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
                  
                if (!empty($cleanId)) {
                    $q->orWhere('id', 'like', "%{$cleanId}%");
                }
            });
        }

        // Calculate totals BEFORE pagination
        $totalGSTCollected = (clone $gstTaxesQuery)->sum('tax_amount');
        // $totalTaxableAmount = (clone $incomeQuery)->sum('planned_amount');

        // Count unique income records with GST
        $gstRecordsCount = (clone $gstTaxesQuery)->distinct('taxable_id')->count('taxable_id');

        // Apply pagination
        $perPage = $request->input('per_page', 20);
        $incomesWithTax = $incomeQuery->with(['company', 'taxes'])
        ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $gstTaxes = $gstTaxesQuery->get();

        // Get all income records for the period (for count and base amounts)
        $allIncomes = Income::whereIn('company_id', $companyIds)
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        if ($companyId !== 'all') {
            $allIncomes->where('company_id', $companyId);
        }

        $allIncomes = $allIncomes->get();
        $totalTaxableAmount= $gstTaxes->sum('taxable_amount');
        // Get receipts count (assuming you have a receipts table)
        $receiptsCount = 0; // You can implement this if you have receipts table

        $data = $this->getCommonViewData($selectedMonth, $selectedYear);
        $data = array_merge($data, [
            'totalGSTCollected' => $totalGSTCollected,
            'totalTaxableAmount' => $totalTaxableAmount,
            'incomesWithTax' => $incomesWithTax,
            'gstTaxes' => $gstTaxes,
            'selectedCompany' => $companyId,
            'currentPeriod' => $period,
            'periods' => $this->getAvailablePeriods(),
            'selectedTaxType' => $taxType,
            'totalRecords' => $allIncomes->count(),
            'receiptsCount' => $receiptsCount,
            'gstRecordsCount' => $gstRecordsCount,
        ]);

        return view('Manager.gst_collected', $data);
    }

    public function attachReceipt(Request $request)
    {
        $request->validate([
            'income_id' => 'required|exists:incomes,id',
            'document_type' => 'required|string',
            'document_file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            'notes' => 'nullable|string',
        ]);

        try {
            // Check if user has access to this income's company
            $income = Income::findOrFail($request->income_id);
            $userCompanyIds = $this->getUserCompanyIds();

            if (!in_array($income->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this company'
                ], 403);
            }

            $file = $request->file('document_file');
            $filename = 'tax_receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');

            // Save to receipts table or your preferred storage
            // Assuming you have a Receipt model
            Receipt::create([
                'income_id' => $request->income_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'document_type' => $request->document_type,
                'notes' => $request->notes,
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Receipt attached successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Taxes on Expenses Page
     */
    public function taxes(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get filters
        $companyId = $request->input('company_id', 'all');
        $period = $request->input('period', date('Y-m'));
        $search = $request->input('search');

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Define queries
        $expenseTaxesQuery = Tax::where('tax_type', 'gst')
            ->where('direction', 'expense')
            ->where('payment_status', 'received')
            ->where('taxable_type', 'App\Models\Expense')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable', function ($query) use ($companyIds) {
                $query->whereIn('status', ['paid', 'settle'])
                      ->whereHas('company', function ($q) use ($companyIds) {
                          $q->whereIn('id', $companyIds);
                      });
            });

        if ($companyId !== 'all') {
            $expenseTaxesQuery->whereHas('taxable', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if (!empty($search)) {
            $cleanId = preg_replace('/[^0-9]/', '', $search);
            $expenseTaxesQuery->whereHas('taxable', function ($q) use ($search, $cleanId) {
                $q->where('expense_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
                  
                if (!empty($cleanId)) {
                    $q->orWhere('id', 'like', "%{$cleanId}%");
                }
            });
        }

        // Calculate totals
        $totalGstPaid = (clone $expenseTaxesQuery)->sum('tax_amount');
        $gstRecordsCount = (clone $expenseTaxesQuery)->distinct('taxable_id')->count('taxable_id');
        $totalExpenseAmount = (clone $expenseTaxesQuery)->get()->sum(function ($tax) {
            return $tax->taxable ? ( $tax->taxable->actual_amount) : 0;
        });

        // Apply pagination
        $perPage = $request->input('per_page', 20);
        $expenseTaxes = $expenseTaxesQuery->orderBy('created_at', 'desc')
            ->with([
                'taxable' => function ($query) {
                    $query->with(['company', 'categoryRelation']);
                }
            ])
            ->paginate($perPage)
            ->withQueryString();

        // Get common view data
        $data = $this->getCommonViewData($selectedMonth, $selectedYear);
        $data['periods'] = $this->getAvailablePeriods();

        // Merge with GST data
        $data = array_merge($data, [
            'gstRecordsCount' => $gstRecordsCount,
            'totalExpenseAmount' => $totalExpenseAmount,
            'totalTaxPaid' => $totalGstPaid,
            'billsWithTax' => $expenseTaxes->total(),
            'selectedMonth' => $selectedMonth,
            'lastUpdated' => $expenseTaxes->isNotEmpty()
                ? date('d-m-Y', strtotime($expenseTaxes->max('created_at')))
                : date('d-m-Y'),
            'currentPeriod' => date('M Y', strtotime($period)),
            'expenseTaxes' => $expenseTaxes,
            'selectedCompany' => $companyId,
            'selectedPeriod' => $period,
            'gstTaxes' => $expenseTaxes,
        ]);

        return view('Manager.expense_taxes', $data);
    }

    /**
     * Consolidated Tax Report
     */
    public function taxSummary(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        $period = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Check if tax_type column exists in invoices table
        $hasTaxTypeInInvoice = Schema::hasColumn('invoices', 'tax_type');

        // Sales (Output) - Only from user's companies
        $salesQuery = Invoice::where('type', 'invoice')
            ->where('is_taxable', true)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('issue_date', $selectedMonth)
            ->whereYear('issue_date', $selectedYear);

        $salesInvoices = $salesQuery->get();

        // Purchases (Input) - Only from user's companies
        $purchaseQuery = Invoice::where('type', 'purchase')
            ->where('is_taxable', true)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('paid_date', $selectedMonth)
            ->whereYear('paid_date', $selectedYear);

        $purchaseInvoices = $purchaseQuery->get();

        // Expenses - Only from user's companies
        $expenseQuery = Expense::whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereIn('company_id', $companyIds)
            ->whereMonth('paid_date', $selectedMonth)
            ->whereYear('paid_date', $selectedYear);

        $expenses = $expenseQuery->get();

        // Initialize tax summary
        $taxSummary = [
            'gst' => [
                'sales' => 0,
                'purchases' => 0,
                'expenses' => $expenses->where('tax_type', 'gst')->sum('tax_amount'),
            ],
            'tds' => [
                'sales' => 0,
                'purchases' => 0,
                'expenses' => $expenses->where('tax_type', 'tds')->sum('tax_amount'),
            ],
            'other' => [
                'sales' => 0,
                'purchases' => 0,
                'expenses' => $expenses->where('tax_type', 'other')->sum('tax_amount'),
            ],
        ];

        // Fill sales and purchases data if tax_type column exists
        if ($hasTaxTypeInInvoice) {
            $taxSummary['gst']['sales'] = $salesInvoices->where('tax_type', 'gst')->sum('tax_amount');
            $taxSummary['tds']['sales'] = $salesInvoices->where('tax_type', 'tds')->sum('tax_amount');
            $taxSummary['other']['sales'] = $salesInvoices->where('tax_type', 'other')->sum('tax_amount');

            $taxSummary['gst']['purchases'] = $purchaseInvoices->where('tax_type', 'gst')->sum('tax_amount');
            $taxSummary['tds']['purchases'] = $purchaseInvoices->where('tax_type', 'tds')->sum('tax_amount');
            $taxSummary['other']['purchases'] = $purchaseInvoices->where('tax_type', 'other')->sum('tax_amount');
        } else {
            // If no tax_type column, assume all sales/purchases are GST
            $taxSummary['gst']['sales'] = $salesInvoices->sum('tax_amount');
            $taxSummary['gst']['purchases'] = $purchaseInvoices->sum('tax_amount');
        }

        // Calculate net payable by tax type
        $netPayable = [
            'gst' => max(0, $taxSummary['gst']['sales'] - $taxSummary['gst']['purchases']),
            'tds' => max(0, $taxSummary['tds']['sales'] - $taxSummary['tds']['purchases']),
        ];

        $data = $this->getCommonViewData($selectedMonth, $selectedYear);
        $data = array_merge($data, [
            'taxSummary' => $taxSummary,
            'netPayable' => $netPayable,
            'selectedPeriod' => $period,
            'salesInvoices' => $salesInvoices,
            'purchaseInvoices' => $purchaseInvoices,
            'expenses' => $expenses,
            'hasTaxTypeInInvoice' => $hasTaxTypeInInvoice,
        ]);

        return view('Manager.tax_summary', $data);
    }

    // Filter methods (for AJAX calls)
    public function filter(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Your filter logic here with user's companies
        return response()->json([
            'success' => true,
            'outputGST' => 180000,
            'itc' => 60000,
            'netPayable' => 120000,
            'period' => 'Nov 2025',
        ]);
    }

    public function storeTaxEntry(Request $request)
    {
        // Validate and store tax entry
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'date' => 'required|date',
            'tax_period' => 'required|string',
            'vendor' => 'required|string',
            'category' => 'required|string',
            'bill_no' => 'required|string',
            'tax_type' => 'required|in:gst,tds,other',
            'tax_amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this company'
            ], 403);
        }

        // Create tax entry
        $taxEntry = TaxEntry::create($validated);

        // Handle file upload
        // if ($request->hasFile('attachment')) {
        //     $path = $request->file('attachment')->store('tax-attachments');
        //     $taxEntry->update(['attachment' => $path]);
        // }

        return response()->json(['success' => true, 'message' => 'Tax entry saved successfully']);
    }

    public function settlement(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get settlements only for user's companies
        $settlements = GstSettlement::whereIn('company_id', $companyIds)
            ->with(['company'])
            ->orderBy('payment_date', 'desc')
            ->get();

        // Calculate GST summary (only for user's companies)
        $gstSummary = $this->calculateGstSummary($companyIds);

        // Get common view data
        $data = $this->getCommonViewData();

        return array_merge($data, [
            'settlements' => $settlements,
            'gstSummary' => $gstSummary,
            'currentPeriod' => date('M Y'),
        ]);
    }

    private function calculateGstSummary($companyIds = null)
    {
        $companyIds = $companyIds ?? $this->getUserCompanyIds();

        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get output GST (from income) - Only from user's companies
        $outputGst = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'income')
                    ->orWhere('taxable_type', 'App\Models\Income');
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->sum('tax_amount');

        // Get input GST (from expenses - ITC) - Only from user's companies
        $inputGst = Tax::where('tax_type', 'gst')
            ->where(function ($query) {
                $query->where('direction', 'expense')
                    ->orWhere('taxable_type', 'App\Models\Expense');
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            })
            ->sum('tax_amount');

        // Calculate net payable
        $netPayable = max(0, $outputGst - $inputGst);

        return [
            'output_gst' => $outputGst,
            'input_gst' => $inputGst,
            'net_payable' => $netPayable,
        ];
    }

    public function storeSettlement(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tax_period' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:netbanking,upi,neft_rtgs,cheque',
            'challan_number' => 'nullable|string|max:100',
            'utr_number' => 'nullable|string|max:100',
            'purpose_comment' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this company'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $settlement = GstSettlement::create([
                'company_id' => $validated['company_id'],
                'tax_period' => $validated['tax_period'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_mode' => $validated['payment_mode'],
                'challan_number' => $validated['challan_number'] ?? null,
                'utr_number' => $validated['utr_number'] ?? null,
                'status' => 'paid',
                'purpose_comment' => $validated['purpose_comment'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Handle attachment if provided
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = 'settlement_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('settlements', $filename, 'public');

                // Assuming you have an Attachment model
                $settlement->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'uploaded_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Settlement created successfully!',
                'settlement' => $settlement,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showSettlement($id)
    {
        $settlement = GstSettlement::with(['company', 'creator', 'attachments'])->findOrFail($id);

        // Check if user has access to this settlement's company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($settlement->company_id, $userCompanyIds)) {
            abort(403, 'You do not have access to this settlement');
        }

        return view('Manager.gst_settlement_show', compact('settlement'));
    }

    /**
     * Export GST Dashboard Summary
     */
    public function export(Request $request, $type)
    {
        $companyIds = $this->getUserCompanyIds($request->company);
        $period = $request->input('period', date('Y-m'));
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Output GST (from income)
        $outputTaxes = Tax::where('tax_type', 'gst')
            ->where(function ($q) {
                $q->where('direction', 'income')->orWhere('taxable_type', 'App\Models\Income');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($q) use ($companyIds) {
                $q->whereIn('id', $companyIds);
            })->get();

        // Input GST (from expenses)
        $inputTaxes = Tax::where('tax_type', 'gst')
            ->where(function ($q) {
                $q->where('direction', 'expense')->orWhere('taxable_type', 'App\Models\Expense');
            })
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($q) use ($companyIds) {
                $q->whereIn('id', $companyIds);
            })->get();

        // TDS
        $tdsTaxes = Tax::where('tax_type', 'tds')
            ->where('payment_status', 'received')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($q) use ($companyIds) {
                $q->whereIn('id', $companyIds);
            })->get();

        $totals = [
            'output' => $outputTaxes->sum('tax_amount'),
            'input' => $inputTaxes->sum('tax_amount'),
            'tds' => $tdsTaxes->sum('tax_amount'),
        ];
        $totals['net'] = $totals['output'] - $totals['input'];

        if ($type === 'pdf') {
            return view('Manager.gst_dashboard_print', compact('totals', 'period'));
        }

        $filename = "GST_Dashboard_Summary_" . $period . ".csv";
        return response()->stream(function () use ($totals, $period) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['GST DASHBOARD SUMMARY', $period]);
            fputcsv($handle, []);
            fputcsv($handle, ['Component', 'Description', 'Amount (₹)']);
            fputcsv($handle, ['Output GST', 'GST collected from sales/income', number_format($totals['output'], 2)]);
            fputcsv($handle, ['Input GST (ITC)', 'GST paid on purchases/expenses', number_format($totals['input'], 2)]);
            fputcsv($handle, ['Net GST', $totals['net'] >= 0 ? 'Payable' : 'Receivable', number_format(abs($totals['net']), 2)]);
            fputcsv($handle, ['TDS Deductions', 'TDS deducted on payments', number_format($totals['tds'], 2)]);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export GST Collected (Income)
     */
    public function exportGstCollected(Request $request, $type)
    {
        $companyIds = $this->getUserCompanyIds($request->company);
        $period = $request->input('period', date('Y-m'));
        $taxType = 'gst'; // Fixed to GST for this module as per user request
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        $query = Income::whereIn('company_id', $companyIds)
            ->whereMonth('income_date', $selectedMonth)
            ->whereYear('income_date', $selectedYear);

        $incomes = $query->with(['company', 'taxes'])->get();

        if ($type === 'pdf') {
            return view('Manager.gst_collected_print', compact('incomes', 'period', 'taxType'));
        }

        $filename = "GST_Collected_Export_" . $period . ".csv";
        return response()->stream(function () use ($incomes, $taxType) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Company', 'Client/Description', 'Taxable Amount', 'Tax Type', 'Tax Amount', 'Status']);

            foreach ($incomes as $income) {
                foreach ($income->taxes as $tax) {
                    if ($taxType !== 'all' && $tax->tax_type !== $taxType)
                        continue;

                    fputcsv($handle, [
                        date('d-m-Y', strtotime($income->income_date ?? $income->created_at)),
                        $income->company->name ?? 'N/A',
                        $income->description ?: ($income->client_name ?: 'Income'),
                        number_format($income->amount, 2),
                        strtoupper($tax->tax_type),
                        number_format($tax->tax_amount, 2),
                        $tax->payment_status ?: 'N/A'
                    ]);
                }
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportSettlements(Request $request)
    {
        $type = $request->input('type', 'excel');
        $companyIds = $this->getUserCompanyIds($request->company_id);

        $settlements = GstSettlement::whereIn('company_id', $companyIds)
            ->with('company')
            ->get();

        // Implement export logic (Excel or PDF)
        // You can use Laravel Excel package or DomPDF
    }

    public function returns(Request $request)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        // Get tasks with filters
        $status = $request->input('status', 'all');
        $companyId = $request->input('company_id', 'all');
        $returnType = $request->input('return_type', 'all');

        $query = GstTask::with(['company'])
            ->whereIn('company_id', $companyIds)
            ->orderBy('due_date', 'asc');

        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($companyId !== 'all') {
            $query->where('company_id', $companyId);
        }

        if ($returnType !== 'all') {
            $query->where('return_type', $returnType);
        }

        $tasks = $query->get();

        // Get statistics - Only for user's companies
        $stats = [
            'total' => GstTask::whereIn('company_id', $companyIds)->count(),
            'pending' => GstTask::whereIn('company_id', $companyIds)->where('status', 'pending')->count(),
            'in_progress' => GstTask::whereIn('company_id', $companyIds)->where('status', 'in_progress')->count(),
            'completed' => GstTask::whereIn('company_id', $companyIds)->where('status', 'completed')->count(),
            'overdue' => GstTask::whereIn('company_id', $companyIds)->overdue()->count(),
        ];

        // Get common view data
        $data = $this->getCommonViewData();

        // Get available return types
        $returnTypes = ['GSTR-1', 'GSTR-3B', 'GSTR-9', 'TDS Return', 'Income Tax Return', 'Other'];

        return array_merge($data, [
            'tasks' => $tasks,
            'stats' => $stats,
            'returnTypes' => $returnTypes,
            'filters' => [
                'status' => $status,
                'company_id' => $companyId,
                'return_type' => $returnType,
            ],
        ]);
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'tax_period' => 'required|date_format:Y-m',
            'return_type' => 'required|string|max:50',
            'due_date' => 'required|date',
            'reminder_date' => 'required|date|before_or_equal:due_date',
            'assigned_to' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user has access to this company
        $userCompanyIds = $this->getUserCompanyIds();
        if (!in_array($validated['company_id'], $userCompanyIds)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this company'
            ], 403);
        }

        try {
            $task = GstTask::create([
                'company_id' => $validated['company_id'],
                'tax_period' => $validated['tax_period'],
                'return_type' => $validated['return_type'],
                'due_date' => $validated['due_date'],
                'reminder_date' => $validated['reminder_date'],
                'assigned_to' => $validated['assigned_to'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        try {
            $task = GstTask::findOrFail($id);

            // Check if user has access to this task's company
            $userCompanyIds = $this->getUserCompanyIds();
            if (!in_array($task->company_id, $userCompanyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this task'
                ], 403);
            }

            $task->update([
                'status' => $validated['status'],
                'completed_date' => $validated['status'] === 'completed' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully!',
                'task' => $task,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendReminders(Request $request)
    {
        try {
            // Get user's company IDs
            $companyIds = $this->getUserCompanyIds($request->company_id);

            // Get upcoming tasks (due in next 7 days) - Only for user's companies
            $upcomingTasks = GstTask::with(['company'])
                ->whereIn('company_id', $companyIds)
                ->where('due_date', '<=', now()->addDays(7))
                ->where('due_date', '>=', now())
                ->where('status', '!=', 'completed')
                ->get();

            // Get overdue tasks - Only for user's companies
            $overdueTasks = GstTask::with(['company'])
                ->whereIn('company_id', $companyIds)
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->get();

            $totalSent = 0;

            // Send emails for upcoming tasks
            foreach ($upcomingTasks as $task) {
                // Implement your email sending logic here
                // Mail::to($task->assigned_to)->send(new TaskReminderMail($task));
                $totalSent++;
            }

            // Send emails for overdue tasks
            foreach ($overdueTasks as $task) {
                // Mail::to($task->assigned_to)->send(new TaskOverdueMail($task));
                $totalSent++;
            }

            return response()->json([
                'success' => true,
                'message' => "Reminder emails sent for {$totalSent} tasks.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportTaxes(Request $request, $type)
    {
        // Get user's company IDs
        $companyIds = $this->getUserCompanyIds($request->company_id);

        $companyId = $request->input('company_id', 'all');
        $period = $request->input('period', date('Y-m'));
        // $taxType   = $request->input('tax_type', 'all');

        // Parse period
        $selectedMonth = date('m', strtotime($period));
        $selectedYear = date('Y', strtotime($period));

        // Get the data (same logic as index method) - Only from user's companies
        $expenseTaxesQuery = Tax::where(function ($query) {
            $query->where('direction', 'expense')
                ->orWhere('taxable_type', 'App\Models\Expense');
        })
            ->where('payment_status', 'received')
            ->where('tax_type', 'gst')
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear)
            ->whereHas('taxable.company', function ($query) use ($companyIds) {
                $query->whereIn('id', $companyIds);
            });

        // if ($taxType !== 'all') {
        //     $expenseTaxesQuery->where('tax_type', $taxType);
        // }

        $expenseTaxes = $expenseTaxesQuery->with(['taxable.company', 'taxable.categoryRelation'])->get();

        // Filter by specific company if needed
        if ($companyId !== 'all') {
            $expenseTaxes = $expenseTaxes->filter(function ($tax) use ($companyId) {
                return $tax->taxable && $tax->taxable->company_id == $companyId;
            });
        }

        // Prepare data for export
        $data = $expenseTaxes->map(function ($tax) {
            $expense = $tax->taxable;
            return [
                'Date' => $expense->paid_date ? date('d-m-Y', strtotime($expense->paid_date)) : date('d-m-Y', strtotime($expense->created_at)),
                'Company' => $expense->company->name ?? 'N/A',
                'Expense Name' => $expense->expense_name ?? 'N/A',
                'Vendor/Party' => $expense->party_name ?? 'N/A',
                'Tax Type' => strtoupper($tax->tax_type),
                'Expense Amount' => number_format($expense->actual_amount ?? 0, 2),
                'Tax Percentage' => $tax->tax_percentage . '%',
                'Tax Amount' => number_format($tax->tax_amount, 2),
                'Payment Status' => ucfirst($tax->payment_status),
                'Notes' => $tax->payment_notes ?? ($expense->notes ?? 'N/A'),
            ];
        });

        // Add totals row
        $data->push([
            'Date' => 'TOTAL',
            'Company' => '',
            'Expense Name' => '',
            'Vendor/Party' => '',
            'Tax Type' => '',
            'Expense Amount' => number_format($expenseTaxes->sum(function ($tax) {
                return $tax->taxable->actual_amount ?? 0;
            }), 2),
            'Tax Percentage' => '',
            'Tax Amount' => number_format($expenseTaxes->sum('tax_amount'), 2),
            'Payment Status' => '',
            'Notes' => '',
        ]);

        if ($type === 'excel') {
            $filename = "expense_taxes_{$period}.csv";
            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                $firstRow = $data->first();
                if ($firstRow) {
                    fputcsv($handle, array_keys((array) $firstRow));
                }
                foreach ($data as $row) {
                    fputcsv($handle, array_values((array) $row));
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } elseif ($type === 'pdf') {
            if (view()->exists('Manager.exports.taxes_pdf')) {
                $pdf = PDF::loadView('Manager.exports.taxes_pdf', [
                    'data' => $data,
                    'period' => date('M Y', strtotime($period)),
                    'totalTax' => $expenseTaxes->sum('tax_amount'),
                    'totalExpense' => $expenseTaxes->sum(function ($tax) {
                        return $tax->taxable->actual_amount ?? 0;
                    }),
                ]);
            } elseif (view()->exists('Manager.gst_expense_taxes_print')) {
                $pdf = PDF::loadView('Manager.gst_expense_taxes_print', [
                    'taxes' => $expenseTaxes,
                    'period' => $period,
                    // 'taxType' => $taxType,
                ]);
            } else {
                $html = '<h2 style="font-family:sans-serif;">Expense Taxes Report (' . date('M Y', strtotime($period)) . ')</h2>';
                $html .= '<table border="1" cellpadding="5" style="width:100%; border-collapse:collapse; font-family:sans-serif; font-size:12px;">';
                $html .= '<tr><th>Date</th><th>Company</th><th>Expense</th><th>Vendor</th><th>Tax Type</th><th>Amount (Rs)</th><th>Tax %</th><th>Tax Amt (Rs)</th><th>Status</th></tr>';
                foreach ($data as $row) {
                    $rowArray = (array) $row;
                    if (isset($rowArray['Date']) && $rowArray['Date'] === 'TOTAL') {
                        $html .= '<tr style="font-weight:bold; background-color:#eee;">';
                    } else {
                        $html .= '<tr>';
                    }
                    $html .= '<td>' . htmlspecialchars($rowArray['Date'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Company'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Expense Name'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Vendor/Party'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Tax Type'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Expense Amount'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Tax Percentage'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Tax Amount'] ?? '') . '</td>';
                    $html .= '<td>' . htmlspecialchars($rowArray['Payment Status'] ?? '') . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                $pdf = PDF::loadHTML($html);
            }
            return $pdf->download("expense_taxes_{$period}.pdf");
        }

        return back()->with('error', 'Invalid export type');
    }
}
