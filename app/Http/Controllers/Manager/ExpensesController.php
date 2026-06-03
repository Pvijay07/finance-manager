<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Receipt;
use App\Models\Tax;

class ExpensesController extends Controller
{
  public function index(Request $request)
  {
    // Get the authenticated user
    $user = auth()->user();

    // Get filters from request
    $companyId = $request->get('company');
    $categoryId = $request->get('category');
    $status = $request->get('status', 'all');
    $type = $request->get('type', 'all');
    $dateRange = $request->get('date_range', 'month');
    $activeTab = $request->get('tab', 'standard');

    // Build base query
    $query = Expense::with(['company', 'categoryRelation', 'receipts', 'parent', 'children']);
    // Apply company filter - only show expenses from companies where user is manager
    $query->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    // Apply specific company filter if selected
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    // Apply type filter
    if ($type === 'standard') {
      $query->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $query->where('source', 'manual');
    }

    // Apply category filter
    if ($categoryId && $categoryId !== 'all') {
      $query->where('category_id', $categoryId);
    }

    // Apply status filter
    if ($status && $status !== 'all') {
      $query->where('status', $status);
    }

    // Apply date range filter
    $now = Carbon::now();
    $startDate = null;
    $endDate = null;

    switch ($dateRange) {
      case 'today':
        $startDate = $now->copy()->startOfDay();
        $endDate = $now->copy()->endOfDay();
        $dateRangeTitle = 'Today';
        break;
      case 'week':
        $startDate = $now->copy()->startOfWeek();
        $endDate = $now->copy()->endOfWeek();
        $dateRangeTitle = 'This Week';
        break;
      case 'month':
        $startDate = $now->copy()->startOfMonth();
        $endDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->copy()->format('M-Y');
        break;
      case 'quarter':
        $startDate = $now->copy()->startOfQuarter();
        $endDate = $now->copy()->endOfQuarter();
        $dateRangeTitle = 'Q' . ceil($now->month / 3) . ' ' . $now->year;
        break;
      case 'year':
        $startDate = $now->copy()->startOfYear();
        $endDate = $now->copy()->endOfYear();
        $dateRangeTitle = $now->year;
        break;
      case 'custom':
        // Handle custom date range
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null;
        $dateRangeTitle = 'Custom Range';
        break;
      default:
        $startDate = $now->copy()->startOfMonth();
        $endDate = $now->copy()->endOfMonth();
        $dateRangeTitle = $now->copy()->format('M-Y');
    }

    if ($startDate && $endDate) {
      $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Get per page count
    $perPage = $request->get('per_page', 10);

    // Get all expenses for table WITH PAGINATION
    $allExpenses = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

    // Calculate summary statistics - Need a separate query for accurate totals
    $statsQuery = Expense::with(['company', 'categoryRelation', 'receipts']);

    // Apply the same filters to stats query, including user's companies
    $statsQuery->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    if ($companyId) {
      $statsQuery->where('company_id', $companyId);
    }
    if ($type === 'standard') {
      $statsQuery->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $statsQuery->where('source', 'manual');
    }
    if ($categoryId && $categoryId !== 'all') {
      $statsQuery->where('category_id', $categoryId);
    }
    if ($status && $status !== 'all') {
      $statsQuery->where('status', $status);
    }
    if ($startDate && $endDate) {
      $statsQuery->whereBetween('created_at', [$startDate, $endDate]);
    }

    $allExpensesForStats = $statsQuery->get();

    // Calculate summary statistics using $allExpensesForStats
    $totalPayments = $allExpensesForStats->sum('planned_amount');
    $totalItems = $allExpensesForStats->count();

    // Paid expenses
    $paidExpenses = $allExpensesForStats->where('status', 'paid');
    $paidAmount = $paidExpenses->sum('planned_amount');
    $paidCount = $paidExpenses->count();

    // Pending expenses
    $pendingExpenses = $allExpensesForStats->where('status', 'pending');
    $pendingAmount = $pendingExpenses->sum('planned_amount');
    $pendingCount = $pendingExpenses->count();

    // Overdue expenses
    $overdueExpenses = $allExpensesForStats->where('status', 'overdue');
    $overdueAmount = $overdueExpenses->sum('planned_amount');
    $overdueCount = $overdueExpenses->count();

    // Total overdue (all time) - only for user's companies
    $totalOverdueQuery = Expense::where('status', 'overdue');
    $totalOverdueQuery->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    if ($companyId) {
      $totalOverdueQuery->where('company_id', $companyId);
    }
    if ($type === 'standard') {
      $totalOverdueQuery->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $totalOverdueQuery->where('source', 'manual');
    }
    if ($categoryId && $categoryId !== 'all') {
      $totalOverdueQuery->where('category_id', $categoryId);
    }
    $totalOverdueAmount = $totalOverdueQuery->sum('planned_amount');
    $totalOverdueCount = $totalOverdueQuery->count();

    // Next 7 days expenses - only for user's companies
    $next7DaysStart = Carbon::now();
    $next7DaysEnd = Carbon::now()->addDays(7);
    $next7DaysQuery = Expense::whereBetween('due_date', [$next7DaysStart, $next7DaysEnd]);

    $next7DaysQuery->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    if ($companyId) {
      $next7DaysQuery->where('company_id', $companyId);
    }
    if ($type === 'standard') {
      $next7DaysQuery->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $next7DaysQuery->where('source', 'manual');
    }
    if ($categoryId && $categoryId !== 'all') {
      $next7DaysQuery->where('category_id', $categoryId);
    }

    $next7DaysAmount = $next7DaysQuery->sum('planned_amount');
    $next7DaysCount = $next7DaysQuery->count();

    // Get companies where the user is the manager (not all companies)
    $companies = Company::where('manager_id', $user->id)
      ->where('status', 'active')
      ->get();

    $categories = Category::where(['is_active' => true, 'category_type' => 'not_standard'])->get();

    return view('Manager.expenses.index', compact(
      'allExpenses',
      'companies',
      'categories',
      'companyId',
      'categoryId',
      'status',
      'type',
      'dateRange',
      'dateRangeTitle',
      'activeTab',
      'startDate',
      'endDate',
      'totalPayments',
      'totalItems',
      'paidAmount',
      'paidCount',
      'pendingAmount',
      'pendingCount',
      'overdueAmount',
      'overdueCount',
      'totalOverdueAmount',
      'totalOverdueCount',
      'next7DaysAmount',
      'next7DaysCount'
    ));
  }
  public function splitHistory($id)
  {
    try {
      $expense = Expense::with(['parent', 'children', 'taxes'])->findOrFail($id);

      // Traverse up to find the ultimate root expense
      $rootExpense = $expense;
      while ($rootExpense->parent_id) {
        $rootExpense = Expense::findOrFail($rootExpense->parent_id);
      }
      $rootId = $rootExpense->id;

      // Recursively get all parts of the split (descendants)
      $allSplits = collect([$rootExpense]);
      $toProcess = collect([$rootId]);

      while ($toProcess->isNotEmpty()) {
        $nextLevel = Expense::with('taxes')->whereIn('parent_id', $toProcess)->get();
        if ($nextLevel->isEmpty())
          break;
        $allSplits = $allSplits->concat($nextLevel);
        $toProcess = $nextLevel->pluck('id');
      }

      $allSplits = $allSplits->unique('id')->sortBy('created_at')->values();

      $totalPaid = $allSplits->whereIn('status', ['paid', 'settle'])->sum('planned_amount');
      $totalBalance = $allSplits->whereNotIn('status', ['paid', 'settle'])->sum('planned_amount');
      $originalSum = $allSplits->sum('planned_amount');

      $originalGstAmount = $allSplits->sum(fn($split) => $split->taxes->where('tax_type', 'gst')->sum('tax_amount'));
      $originalTdsAmount = $allSplits->sum(fn($split) => $split->taxes->where('tax_type', 'tds')->sum('tax_amount'));

      $tdsBalance = $allSplits->whereNotIn('status', ['paid', 'settle'])
                              ->sum(fn($split) => $split->taxes->where('tax_type', 'tds')->sum('tax_amount'));

      $rootGst = $rootExpense->taxes->where('tax_type', 'gst')->first();
      $rootTds = $rootExpense->taxes->where('tax_type', 'tds')->first();
      $gstPercentage = $rootGst ? $rootGst->tax_percentage : 0;
      $tdsPercentage = $rootTds ? $rootTds->tax_percentage : 0;

      return response()->json([
        'success' => true,
        'current_expense' => [
          'id' => $expense->id,
          'planned_amount' => $expense->planned_amount,
          'status' => $expense->status
        ],
        'parent_expense' => $expense->parent_id ? [
          'id' => $rootExpense->id,
          'planned_amount' => $originalSum,
          'original_total' => $rootExpense->original_amount ?? $rootExpense->schedule_amount ?? $originalSum,
          'status' => $rootExpense->status,
          'created_at' => $rootExpense->created_at->toIso8601String(),
          'gst_amount' => $originalGstAmount,
          'tds_amount' => $originalTdsAmount,
          'gst_percentage' => $gstPercentage,
          'tds_percentage' => $tdsPercentage
        ] : null,
        'children' => $allSplits->map(function ($split) {
          return [
            'id' => $split->id,
            'planned_amount' => $split->planned_amount,
            'actual_amount' => $split->actual_amount,
            'status' => $split->status,
            'created_at' => $split->created_at->toIso8601String(),
            'paid_date' => $split->paid_date,
            'due_date' => $split->due_date,
            'gst_amount' => $split->taxes->where('tax_type', 'gst')->sum('tax_amount'),
            'tds_amount' => $split->taxes->where('tax_type', 'tds')->sum('tax_amount')
          ];
        })->values(),
        'summary' => [
          'original_amount' => $originalSum,
          'total_paid' => $totalPaid,
          'total_balance' => $totalBalance,
          'tds_balance' => $tdsBalance,
          'split_count' => $allSplits->count()
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error loading split history: ' . $e->getMessage()
      ]);
    }
  }


  // Add this method for AJAX summary updates
  public function getSummary(Request $request)
  {

    $companyId = $request->get('company');
    $categoryId = $request->get('category');
    $status = $request->get('status', 'all');
    $type = $request->get('type', 'all');
    $dateRange = $request->get('date_range', 'month');

    // Build base query
    $query = Expense::with(['company', 'categoryRelation']);

    // Get the authenticated user
    $user = auth()->user();

    // Apply company filter - only show expenses from companies where user is manager
    $query->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    // Apply company filter if specifically selected
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    // Apply type filter
    if ($type === 'standard') {
      $query->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $query->where('source', 'manual');
    }

    // Apply category filter
    if ($categoryId && $categoryId !== 'all') {
      $query->where('category_id', $categoryId);
    }

    // Apply status filter
    if ($status && $status !== 'all') {
      $query->where('status', $status);
    }

    // Apply date range filter
    $now = Carbon::now();
    switch ($dateRange) {
      case 'today':
        $query->whereDate('created_at', $now->toDateString());
        $dateRangeTitle = 'Today';
        break;
      case 'week':
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        $dateRangeTitle = 'This Week';
        break;
      case 'month':
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $dateRangeTitle = $now->copy()->format('M-Y');
        break;
      case 'quarter':
        $startOfQuarter = $now->copy()->startOfQuarter();
        $endOfQuarter = $now->copy()->endOfQuarter();
        $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
        $dateRangeTitle = 'Q' . ceil($now->month / 3) . ' ' . $now->year;
        break;
      case 'year':
        $startOfYear = $now->copy()->startOfYear();
        $endOfYear = $now->copy()->endOfYear();
        $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
        $dateRangeTitle = $now->year;
        break;
      case 'custom':
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null;
        if ($startDate && $endDate) {
          $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $dateRangeTitle = 'Custom Range';
        break;
      default:
        $dateRangeTitle = $now->copy()->format('M-Y');
    }

    // Get all expenses for table
    $allExpenses = $query->orderBy('created_at', 'desc')->get();

    // Calculate summary statistics
    $totalPayments = $allExpenses->sum('planned_amount');
    $totalItems = $allExpenses->count();

    // Paid expenses
    $paidExpenses = $allExpenses->where('status', 'paid');
    $paidAmount = $paidExpenses->sum('actual_amount');
    $paidCount = $paidExpenses->count();

    // Pending expenses
    $pendingExpenses = $allExpenses->where('status', 'pending');
    $pendingAmount = $pendingExpenses->sum('planned_amount');
    $pendingCount = $pendingExpenses->count();

    // Overdue expenses (for current date range)
    $overdueExpenses = $allExpenses->where('status', 'overdue');
    $overdueAmount = $overdueExpenses->sum('planned_amount');
    $overdueCount = $overdueExpenses->count();

    // Total overdue (all time)
    $totalOverdueQuery = Expense::where('status', 'overdue');
    if ($companyId) {
      $totalOverdueQuery->where('company_id', $companyId);
    }
    $totalOverdueAmount = $totalOverdueQuery->sum('planned_amount');
    $totalOverdueCount = $totalOverdueQuery->count();

    // Next 7 days expenses
    $next7DaysStart = Carbon::now();
    $next7DaysEnd = Carbon::now()->addDays(7);
    $next7DaysQuery = Expense::whereBetween('due_date', [$next7DaysStart, $next7DaysEnd]);

    if ($companyId) {
      $next7DaysQuery->where('company_id', $companyId);
    }
    if ($type === 'standard') {
      $next7DaysQuery->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $next7DaysQuery->where('source', 'manual');
    }

    $next7DaysAmount = $next7DaysQuery->sum('planned_amount');
    $next7DaysCount = $next7DaysQuery->count();

    // Get companies and categories for dropdowns
    $companies = Company::where('status', 'active')->get();
    $categories = Category::where('is_active', true)->get();

    return response()->json([
      'dateRangeTitle' => $dateRangeTitle,
      'totalPayments' => $totalPayments,
      'totalItems' => $totalItems,
      'paidAmount' => $paidAmount,
      'paidCount' => $paidCount,
      'pendingAmount' => $pendingAmount,
      'pendingCount' => $pendingCount,
      'overdueAmount' => $overdueAmount,
      'overdueCount' => $overdueCount,
      'totalOverdueAmount' => $totalOverdueAmount,
      'totalOverdueCount' => $totalOverdueCount,
      'next7DaysAmount' => $next7DaysAmount,
      'next7DaysCount' => $next7DaysCount,
    ]);
  }

  // Add this method for AJAX table updates
  public function getTable(Request $request)
  {
    // Same query logic as above, but returns only table HTML

    $companyId = $request->get('company');
    $categoryId = $request->get('category');
    $status = $request->get('status', 'all');
    $type = $request->get('type', 'all');
    $dateRange = $request->get('date_range', 'month');

    // Build base query
    $query = Expense::with(['company', 'categoryRelation']);

    // Get the authenticated user
    $user = auth()->user();

    // Apply company filter - only show expenses from companies where user is manager
    $query->whereHas('company', function ($q) use ($user) {
      $q->where('manager_id', $user->id);
    });

    // Apply company filter if specifically selected
    if ($companyId) {
      $query->where('company_id', $companyId);
    }

    // Apply type filter
    if ($type === 'standard') {
      $query->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $query->where('source', 'manual');
    }

    // Apply category filter
    if ($categoryId && $categoryId !== 'all') {
      $query->where('category_id', $categoryId);
    }

    // Apply status filter
    if ($status && $status !== 'all') {
      $query->where('status', $status);
    }

    // Apply date range filter
    $now = Carbon::now();
    switch ($dateRange) {
      case 'today':
        $query->whereDate('created_at', $now->toDateString());
        $dateRangeTitle = 'Today';
        break;
      case 'week':
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        $dateRangeTitle = 'This Week';
        break;
      case 'month':
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $dateRangeTitle = $now->copy()->format('M-Y');
        break;
      case 'quarter':
        $startOfQuarter = $now->copy()->startOfQuarter();
        $endOfQuarter = $now->copy()->endOfQuarter();
        $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
        $dateRangeTitle = 'Q' . ceil($now->month / 3) . ' ' . $now->year;
        break;
      case 'year':
        $startOfYear = $now->copy()->startOfYear();
        $endOfYear = $now->copy()->endOfYear();
        $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
        $dateRangeTitle = $now->year;
        break;
      case 'custom':
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null;
        if ($startDate && $endDate) {
          $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        $dateRangeTitle = 'Custom Range';
        break;
      default:
        $dateRangeTitle = $now->copy()->format('M-Y');
    }

    // Get all expenses for table
    $allExpenses = $query->orderBy('created_at', 'desc')->get();

    // Calculate summary statistics
    $totalPayments = $allExpenses->sum('planned_amount');
    $totalItems = $allExpenses->count();

    // Paid expenses
    $paidExpenses = $allExpenses->where('status', 'paid');
    $paidAmount = $paidExpenses->sum('actual_amount');
    $paidCount = $paidExpenses->count();

    // Pending expenses
    $pendingExpenses = $allExpenses->where('status', 'pending');
    $pendingAmount = $pendingExpenses->sum('planned_amount');
    $pendingCount = $pendingExpenses->count();

    // Overdue expenses (for current date range)
    $overdueExpenses = $allExpenses->where('status', 'overdue');
    $overdueAmount = $overdueExpenses->sum('planned_amount');
    $overdueCount = $overdueExpenses->count();

    // Total overdue (all time)
    $totalOverdueQuery = Expense::where('status', 'overdue');
    if ($companyId) {
      $totalOverdueQuery->where('company_id', $companyId);
    }
    $totalOverdueAmount = $totalOverdueQuery->sum('planned_amount');
    $totalOverdueCount = $totalOverdueQuery->count();

    // Next 7 days expenses
    $next7DaysStart = Carbon::now();
    $next7DaysEnd = Carbon::now()->addDays(7);
    $next7DaysQuery = Expense::whereBetween('due_date', [$next7DaysStart, $next7DaysEnd]);

    if ($companyId) {
      $next7DaysQuery->where('company_id', $companyId);
    }
    if ($type === 'standard') {
      $next7DaysQuery->where('source', 'standard');
    } elseif ($type === 'non-standard') {
      $next7DaysQuery->where('source', 'manual');
    }

    $next7DaysAmount = $next7DaysQuery->sum('planned_amount');
    $next7DaysCount = $next7DaysQuery->count();

    // Get companies and categories for dropdowns
    $companies = Company::where('status', 'active')->get();
    $categories = Category::where('is_active', true)->get();

    $allExpenses = $query->orderBy('created_at', 'desc')->get();

    return view('Manager.expenses.partials.table', compact('allExpenses'))->render();
  }

  public function store(Request $request)
  {
    $request->validate([
      'expense_name' => 'required|string|max:255',
      'company_id' => 'nullable|exists:companies,id',
      'category_id' => 'required|exists:categories,id',
      'actual_amount' => 'required|numeric|min:0',
      'status' => 'required|in:upcoming,pending,paid,settle,due,convert_to_tds',
      'payment_mode' => 'nullable|in:cash,bank_transfer,cheque,upi,online',
      'bank_name' => 'nullable|string|max:255',
      'upi_type' => 'nullable|string|max:255',
      'upi_number' => 'nullable|string|max:20',
      'party_name' => 'nullable|string|max:255',
      'mobile_number' => 'nullable|string|max:20',
      'notes' => 'nullable|string',
      'settle_notes' => 'required_if:status,settle,paid|nullable|string',
      'payment_date' => 'nullable|date',
      'due_date' => 'nullable|date',
      'tds_status' => 'nullable|in:received,not_received',

      // Split payment fields for store
      'split_payment' => 'nullable|boolean',
      'create_new_for_balance' => 'nullable|boolean',
      'new_due_date' => 'nullable|date',
      'balance_notes' => 'nullable|string',

      // GST tax fields
      'apply_gst' => 'nullable|in:0,1',
      'gst_percentage' => 'nullable|numeric|min:0|max:100',
      'gst_amount' => 'nullable|numeric|min:0',

      // TDS tax fields
      'apply_tds' => 'nullable|in:0,1',
      'tds_percentage' => 'nullable|numeric|min:0|max:100',
      'tds_amount' => 'nullable|numeric|min:0',
      'grand_total' => 'required|numeric|min:0',

      // Payment schedule fields
      'schedule_amount' => 'nullable|numeric|min:0',
      'paid_amount' => 'nullable|numeric|min:0',
      'balance_amount' => 'nullable|numeric|min:0',

      // File uploads
      'receipts.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
      'tds_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
    ]);

    try {
      DB::beginTransaction();

      // Handle "Convert to TDS" special status
      if ($request->status === 'convert_to_tds') {
        $pAmount = $request->grand_total;
        $rAmount = $request->paid_amount ?? 0;
        $targetTds = $pAmount - $rAmount;

        if ($targetTds > 0) {
          $request->merge([
            'apply_tds' => '1',
            'tds_amount' => $targetTds,
            'tds_percentage' => ($pAmount > 0) ? ($targetTds / $pAmount) * 100 : 0,
            'status' => 'settle'
          ]);
        } else {
          $request->merge(['status' => 'settle']);
        }
      }

      // Calculate values
      $plannedAmount = $request->grand_total;
      $tdsAmount = $request->tds_amount ?? 0;
      $netPayableAmount = $plannedAmount - $tdsAmount;
      $paidAmount = $request->paid_amount ?? 0;
      $actualTotalBase = $request->actual_amount ?? 0;

      // For settle/paid status, if paidAmount is 0, assume full payment
      if ($paidAmount == 0 && in_array($request->status, ['paid', 'settle'])) {
        $paidAmount = $netPayableAmount;
      }

      // Check if this should be a split payment
      $isSplitPayment = $request->status === 'due' &&
        $paidAmount > 0 &&
        $paidAmount < $netPayableAmount;

      $balanceAmount = $netPayableAmount - $paidAmount;

      $gstAmountForCurrent = $request->gst_amount ?? 0;
      $tdsAmountForCurrent = $request->tds_amount ?? 0;

      if ($isSplitPayment && $netPayableAmount > 0) {
        $proportion = $paidAmount / $netPayableAmount;
        $gstAmountForCurrent = ($request->gst_amount ?? 0) * $proportion;
        $tdsAmountForCurrent = ($request->tds_amount ?? 0) * $proportion;

        $paidBaseAmount = $actualTotalBase * $proportion;
        $balanceBaseAmount = $actualTotalBase - $paidBaseAmount;
      } else {
        $paidBaseAmount = $actualTotalBase;
        $balanceBaseAmount = 0;
      }

      $newGstAmount = ($request->gst_amount ?? 0) - $gstAmountForCurrent;
      $newTdsAmount = ($request->tds_amount ?? 0) - $tdsAmountForCurrent;

      // Prepare expense data
      $expenseData = [
        'expense_name' => $request->expense_name,
        'company_id' => $request->company_id,
        'category_id' => $request->category_id,
        'actual_amount' => $isSplitPayment ? $paidBaseAmount : (in_array($request->status, ['paid', 'settle']) ? $actualTotalBase : 0),
        'planned_amount' => $isSplitPayment ? $paidAmount : $plannedAmount,
        'status' => ($isSplitPayment || in_array($request->status, ['paid', 'settle']))
          ? 'paid'
          : ($request->status === 'due'
            ? 'pending'
            : $request->status),
        'source' => 'manual',
        'payment_mode' => $request->payment_mode ?? 'cash',
        'bank_name' => $request->bank_name,
        'upi_type' => $request->upi_type,
        'upi_number' => $request->upi_number,
        'party_name' => $request->party_name,
        'mobile_number' => $request->mobile_number,
        'notes' => $request->notes,
        'settle_notes' => $request->settle_notes,
        'created_by' => auth()->id(),
        'payment_date' => $request->payment_date,
        'due_date' => $request->due_date,
        'is_split' => $isSplitPayment,
        'schedule_amount' => $plannedAmount,
        'paid_amount' => $paidAmount,
        'balance_amount' => $isSplitPayment ? 0 : $balanceAmount,
        'original_amount' => $actualTotalBase
      ];

      // Set paid_date if applicable
      if (in_array($request->status, ['paid', 'settle']) || $isSplitPayment) {
        $expenseData['paid_date'] = $request->payment_date ?? now()->format('Y-m-d');
      }

      // Create the expense
      $expense = Expense::create($expenseData);

      // Handle GST Tax if applied
      if ($request->apply_gst == '1') {
        if (method_exists($this, 'saveTax')) {
          $this->saveTax($expense, 'gst', [
            'tax_percentage' => $request->gst_percentage ?? 0,
            'tax_amount' => $gstAmountForCurrent,
            'amount_paid' => 0,
            'paid_date' => null,
            'payment_status' => 'received',
            'due_date' => $request->payment_date,
          ]);
        } else {
          \App\Models\Tax::create([
            'taxable_type' => Expense::class,
            'taxable_id' => $expense->id,
            'tax_type' => 'gst',
            'tax_percentage' => $request->gst_percentage ?? 0,
            'tax_amount' => $gstAmountForCurrent,
            'amount_paid' => 0,
            'payment_status' => 'received',
            'direction' => 'expense',
          ]);
        }
      }

      // Handle TDS Tax if applied
      if ($request->apply_tds == '1') {
        $tdsPaymentStatus = $request->tds_status ?? 'not_received';
        $tdsPaidDate = $request->tds_status == 'received' ? now()->format('Y-m-d') : null;

        if (method_exists($this, 'saveTax')) {
          $this->saveTax($expense, 'tds', [
            'taxable_type' => Expense::class,
            'tax_percentage' => $request->tds_percentage ?? 0,
            'tax_amount' => $tdsAmountForCurrent,
            'amount_paid' => $request->tds_status == 'paid' ? $tdsAmountForCurrent : 0,
            'paid_date' => $tdsPaidDate,
            'payment_status' => $tdsPaymentStatus,
            'due_date' => $request->payment_date,
          ]);
        } else {
          \App\Models\Tax::create([
            'taxable_type' => Expense::class,
            'taxable_id' => $expense->id,
            'tax_type' => 'tds',
            'tax_percentage' => $request->tds_percentage ?? 0,
            'tax_amount' => $tdsAmountForCurrent,
            'amount_paid' => $request->tds_status == 'paid' ? $tdsAmountForCurrent : 0,
            'paid_date' => $tdsPaidDate,
            'payment_status' => $tdsPaymentStatus,
            'direction' => 'expense',
          ]);
        }
      }

      // Create new expense for balance if this is a split payment
      $newExpenseId = null;
      if ($isSplitPayment && $balanceAmount > 0) {
        $newExpense = $expense->replicate();
        $newExpense->expense_name = $expense->expense_name . ' - Balance';
        $newExpense->planned_amount = $balanceAmount;
        $newExpense->actual_amount = $balanceBaseAmount;
        $newExpense->status = 'pending';
        $newExpense->due_date = $request->new_due_date ?? now()->addDays(30)->format('Y-m-d');
        $newExpense->paid_date = null;
        $newExpense->is_split = true;
        $newExpense->parent_id = $expense->id;
        $newExpense->balance_amount = $balanceAmount;
        $newExpense->schedule_amount = $actualTotalBase;
        $newExpense->notes = $request->balance_notes ?? 'Balance from partial payment of expense #' . $expense->id;
        $newExpense->created_at = now();
        $newExpense->updated_at = now();
        $newExpense->save();

        $newExpenseId = $newExpense->id;

        // Create GST tax for new expense if applicable
        if ($request->apply_gst == '1' && $newGstAmount > 0) {
          \App\Models\Tax::create([
            'taxable_type' => Expense::class,
            'taxable_id' => $newExpenseId,
            'tax_type' => 'gst',
            'tax_amount' => $newGstAmount,
            'tax_percentage' => $request->gst_percentage ?? 0,
            'payment_status' => 'not_received',
            'direction' => 'expense',
          ]);
        }

        // Create TDS tax for new expense if applicable
        if ($request->apply_tds == '1' && $newTdsAmount > 0) {
          \App\Models\Tax::create([
            'taxable_type' => Expense::class,
            'taxable_id' => $newExpenseId,
            'tax_type' => 'tds',
            'tax_amount' => $newTdsAmount,
            'tax_percentage' => $request->tds_percentage ?? 0,
            'payment_status' => 'not_received',
            'direction' => 'expense',
          ]);
        }
      }

      if ($request->hasFile('tds_receipt')) {
        $tdsReceiptFile = $request->file('tds_receipt');
        if ($tdsReceiptFile->isValid()) {
          $filename = 'tds_proof_' . $expense->id . '_' . time() . '_' . uniqid() . '.' . $tdsReceiptFile->getClientOriginalExtension();

          $path = $tdsReceiptFile->storeAs('tds_proofs', $filename, 'public');
          $tdsTax = Tax::where('taxable_type', Expense::class)
            ->where('taxable_id', $expense->id)
            ->where('tax_type', 'tds')
            ->first();

          if ($tdsTax) {
            $tdsTax->update([
              'tds_proof_path' => $path,
              'payment_status' => 'received'
            ]);
          }
        }
      }

      // Handle general receipt uploads
      if ($request->hasFile('receipts')) {
        foreach ($request->file('receipts') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;

            $destinationPath = public_path('uploads/receipts');
            $fileSize = $this->formatBytes($file->getSize());

            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;

            Receipt::create([
              'expense_id' => $expense->id,
              'file_name' => $originalName,
              'file_path' => $filePath,
              'file_type' => $extension,
              'file_size' => $fileSize
            ]);
          }
        }
      }

      DB::commit();

      // Return appropriate response
      if ($request->ajax()) {
        return response()->json([
          'success' => true,
          'message' => $isSplitPayment ?
            'Expense created with partial payment. New expense created for balance.' :
            'Expense created successfully.',
          'new_expense_id' => $newExpenseId,
          'is_split_payment' => $isSplitPayment,
          'data' => [
            'original_expense' => [
              'id' => $expense->id,
              'expense_name' => $expense->expense_name,
              'planned_amount' => $expense->planned_amount,
              'actual_amount' => $expense->actual_amount,
              'balance_amount' => $expense->balance_amount,
              'status' => $expense->status
            ]
          ]
        ]);
      }

      return redirect()->route('manager.expenses')
        ->with('success', $isSplitPayment ?
          'Expense created with partial payment. New expense created for balance.' :
          'Expense created successfully.');
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Expense creation error: ' . $e->getMessage());
      \Log::error('Stack trace: ' . $e->getTraceAsString());

      if ($request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => 'Error creating expense: ' . $e->getMessage()
        ], 500);
      }

      return redirect()->back()
        ->with('error', 'Error creating expense: ' . $e->getMessage())
        ->withInput();
    }
  }

  // Helper method to save tax records (similar to first example)
  private function saveTax($expense, $taxType, $taxData)
  {
    // Make sure tax_amount is not null
    if (!isset($taxData['tax_amount']) || is_null($taxData['tax_amount'])) {
      // Calculate tax amount if not provided
      $taxAmount = 0;

      if ($taxType === 'gst' && isset($taxData['tax_percentage'])) {
        $taxAmount = ($expense->actual_amount * $taxData['tax_percentage']) / 100;
      } elseif ($taxType === 'tds' && isset($taxData['tax_percentage'])) {
        // For TDS, calculate based on whether GST is applied
        $gstApplied = $expense->apply_gst;
        $baseAmount = $expense->actual_amount;

        if ($gstApplied && $expense->gst_percentage) {
          $gstAmount = ($baseAmount * $expense->gst_percentage) / 100;
          $baseAmount += $gstAmount;
        }

        $taxAmount = ($baseAmount * $taxData['tax_percentage']) / 100;
      }

      $taxData['tax_amount'] = $taxAmount;
    }
    $taxData['tax_type'] = $taxType;

    // Ensure tax_amount is not null
    $taxData['tax_amount'] = $taxData['tax_amount'] ?? 0;

    // Add direction and other required fields
    $taxData = array_merge($taxData, [
      'direction' => 'expense',
      'taxable_type' => get_class($expense)
    ]);

    // Find existing tax or create new
    $tax = \App\Models\Tax::where('taxable_type', \App\Models\Expense::class)
      ->where('taxable_id', $expense->id)
      ->where('tax_type', $taxType)
      ->first();

    if ($tax) {
      $tax->update($taxData);
    } else {
      $taxData['taxable_id'] = $expense->id;
      \App\Models\Tax::create($taxData);
    }
  }

  // Helper method to update expense payment status
  private function updateExpensePaymentStatus($expense)
  {
    $taxes = \App\Models\Tax::where('taxable_type', \App\Models\Expense::class)
      ->where('taxable_id', $expense->id)
      ->get();

    $totalTaxAmount = $taxes->sum('tax_amount');
    $totalTaxPaid = $taxes->sum('amount_paid');

    if ($totalTaxAmount > 0) {
      if ($totalTaxPaid >= $totalTaxAmount) {
        $expense->update(['tax_payment_status' => 'fully_paid']);
      } elseif ($totalTaxPaid > 0) {
        $expense->update(['tax_payment_status' => 'partially_paid']);
      } else {
        $expense->update(['tax_payment_status' => 'not_paid']);
      }
    }
  }



  public function showExpense($id)
  {
      try {
          $expense = Expense::with(['company', 'categoryRelation', 'taxes', 'attachments', 'parent.children', 'children'])->find($id);
          
          if (!$expense) {
              return redirect()->back()->with('error', 'Expense not found');
          }

          // Calculate family totals
          $familyExpenses = collect([$expense]);
          if ($expense->parent) {
              $familyExpenses = $familyExpenses->merge([$expense->parent])->merge($expense->parent->children);
          } else {
              $familyExpenses = $familyExpenses->merge($expense->children);
          }
          
          $uniqueFamily = $familyExpenses->unique('id');
          $original_total_amount = $uniqueFamily->sum('actual_amount') ?: $uniqueFamily->sum('planned_amount');
          
          return view('Manager.expenses.expense_view', compact('expense', 'uniqueFamily', 'original_total_amount'));
      } catch (\Exception $e) {
          return redirect()->back()->with('error', 'Failed to load expense details: ' . $e->getMessage());
      }
  }

  public function edit($id)
  {
    try {
      $expense = Expense::with([
        'company',
        'categoryRelation',
        'receipts',
        'taxes' => function ($query) {
          $query->where('direction', 'expense')
            ->where(function ($q) {
              $q->where('tax_type', 'tds')
                ->orWhere('tax_type', 'gst');
            });
        }
      ])
        ->findOrFail($id);

      // Get TDS tax record if exists
      $tdsTax = $expense->taxes->where('tax_type', 'tds')->first();
      $gstTax = $expense->taxes->where('tax_type', 'gst')->first();

      return response()->json([
        'success' => true,
        'id' => $expense->id,
        'parent_id' => $expense->parent_id,
        'expense_name' => $expense->expense_name,
        'planned_amount' => $expense->planned_amount,
        'actual_amount' => $expense->actual_amount,
        'paid_amount' => $expense->paid_amount,
        'balance_amount' => $expense->balance_amount,
        'status' => $expense->status,
        'party_name' => $expense->party_name,
        'mobile_number' => $expense->mobile_number,
        'notes' => $expense->notes,
        'due_date' => $expense->due_date,
        'paid_date' => $expense->paid_date,
        'tax_percentage' => $expense->tax_percentage ?? 0,
        'payment_mode' => $expense->payment_mode ?? 'cash',
        'bank_name' => $expense->bank_name,
        'upi_type' => $expense->upi_type,
        'upi_number' => $expense->upi_number,
        'frequency' => $expense->frequency ?? 'monthly',
        'due_day' => $expense->due_day ?? 1,
        'source' => $expense->source,
        'is_split' => $expense->is_split,
        // TDS related data
        'tds_amount' => $tdsTax ? $tdsTax->tax_amount : 0,
        'tds_percentage' => $tdsTax ? $tdsTax->tax_percentage : 0,
        'tds_status' => $tdsTax ? $tdsTax->payment_status : 'pending',
        'tds_paid_date' => $tdsTax ? $tdsTax->paid_date : null,
        'tds_due_date' => $tdsTax ? $tdsTax->due_date : null,
        'has_tds' => $tdsTax ? true : false,
        'tds_tax_id' => $tdsTax ? $tdsTax->id : null,
        // GST related data
        'gst_amount' => $gstTax ? $gstTax->tax_amount : 0,
        'gst_percentage' => $gstTax ? $gstTax->tax_percentage : 0,
        'gst_status' => $gstTax ? $gstTax->payment_status : 'pending',
        'gst_paid_date' => $gstTax ? $gstTax->paid_date : null,
        'gst_due_date' => $gstTax ? $gstTax->due_date : null,
        'has_gst' => $gstTax ? true : false,
        'gst_tax_id' => $gstTax ? $gstTax->id : null,
        'original_total_base' => $expense->schedule_amount ?? 0, // Sending context for "Actual amount should be 5000"
        // Receipts
        'receipts' => $expense->receipts->map(function ($receipt) {
          return [
            'id' => $receipt->id,
            'file_name' => $receipt->file_name,
            'file_path' => $receipt->file_path,
            'file_size' => $receipt->file_size
          ];
        })
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Expense not found'
      ]);
    }
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'actual_amount' => 'nullable|numeric|min:0',
      'planned_amount' => 'nullable|numeric|min:0',
      'status' => 'required|in:settle,due,convert_to_tds',
      'paid_date' => 'nullable|date',
      'due_date' => 'nullable|date',
      'party_name' => 'nullable|string|max:255',
      'mobile_number' => 'nullable|string|max:20',
      'notes' => 'nullable|string',
      'settle_notes' => 'required_if:status,settle,paid|nullable|string',
      'receipts.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
      'payment_mode' => 'nullable|string|in:cash,bank_transfer,cheque,upi,online',
      'bank_name' => 'nullable|string|max:255',
      'upi_type' => 'nullable|string|max:255',
      'upi_number' => 'nullable|string|max:20',
      'split_payment' => 'nullable|boolean',
      'create_new_for_balance' => 'nullable|boolean',
      'new_due_date' => 'nullable|date',
      'balance_notes' => 'nullable|string',
      // Tax fields
      'apply_gst' => 'nullable|boolean',
      'gst_percentage' => 'nullable|numeric|min:0|max:100',
      'gst_amount' => 'nullable|numeric|min:0',
      'apply_tds' => 'nullable|boolean',
      'tds_percentage' => 'nullable|numeric|min:0|max:100',
      'tds_amount' => 'nullable|numeric|min:0',
      'tds_status' => 'nullable|in:received,not_received',
      'tds_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
      'tds_tax_id' => 'nullable|exists:taxes,id',
      'gst_tax_id' => 'nullable|exists:taxes,id'
    ]);

    try {
      DB::beginTransaction();

      $expense = Expense::findOrFail($id);

      // Handle "Convert to TDS" special status
      if ($request->status === 'convert_to_tds') {
        // Use current data to calculate balance for TDS
        $pAmount = $request->planned_amount ?? $expense->planned_amount;
        $gAmount = $request->boolean('apply_gst') ? ($request->gst_amount ?? 0) : 0;
        $rAmount = $request->actual_amount ?? $expense->actual_amount ?? 0;

        // Calculate needed TDS to clear balance: TDS = (Base + GST) - Paid
        $targetTds = ($pAmount + $gAmount) - $rAmount;

        if ($targetTds > 0) {
          $request->merge([
            'apply_tds' => 1,
            'tds_amount' => $targetTds,
            'tds_percentage' => ($pAmount > 0) ? ($targetTds / $pAmount) * 100 : 0,
            'status' => 'settle'
          ]);
        } else {
          $request->merge(['status' => 'settle']);
        }
      }

      // Calculate values
      $paidAmount = $request->actual_amount ?? $expense->actual_amount ?? 0;
      $originalPlannedAmount = $request->planned_amount ?? $expense->planned_amount;
      $originalGstAmount = $request->gst_amount ?? 0;
      $originalTdsAmount = $request->tds_amount ?? 0;

      // Balance is simply planned minus paid (TDS is tracked separately as a tax record)
      if ($expense->parent_id) {
        $netPayableAmount = $originalPlannedAmount;
      } else {
        $netPayableAmount = $originalPlannedAmount - $originalTdsAmount;
      }


      // Check if this is a split payment
      $isSplitPayment = $request->status === 'due' &&
        $paidAmount > 0 &&
        $paidAmount < $netPayableAmount;
      $balanceAmount = $netPayableAmount - $paidAmount;

      // If split payment, calculate proportional taxes and base amounts
      $gstAmountForCurrent = $originalGstAmount;
      $tdsAmountForCurrent = $originalTdsAmount;
      $originalBaseAmount = $expense->original_amount ?? $expense->actual_amount ?? 0;
      $paidBaseAmount = $originalBaseAmount;
      $balanceBaseAmount = 0;

      if ($isSplitPayment && $netPayableAmount > 0) {
        // Calculate proportion for taxes and base amounts
        $proportion = $paidAmount / $netPayableAmount;
        $gstAmountForCurrent = $originalGstAmount * $proportion;
        $tdsAmountForCurrent = $originalTdsAmount * $proportion;
        $paidBaseAmount = $originalBaseAmount * $proportion;
        $balanceBaseAmount = $originalBaseAmount - $paidBaseAmount;
      } else {
        $proportion = $paidAmount / $netPayableAmount;
        
        $paidBaseAmount = $originalBaseAmount * $proportion;
        $balanceBaseAmount = $originalBaseAmount - $paidBaseAmount;
      }


      if ($isSplitPayment) {
        $expenseData = [
          'expense_name' => $request->expense_name ?? $expense->expense_name,
          'planned_amount' => $paidAmount,
          'actual_amount' => $paidBaseAmount,
          'original_amount' => $originalBaseAmount,
          'status' => 'paid',
          'party_name' => $request->party_name ?? $expense->party_name,
          'mobile_number' => $request->mobile_number ?? $expense->mobile_number,
          'notes' => $request->notes ?? $expense->notes,
          'due_date' => $expense->due_date,
          'is_split' => true,
          'balance_amount' => 0,
          'schedule_amount' => $request->planned_amount ?? $expense->schedule_amount ?? $expense->actual_amount,
          'paid_date' => $request->paid_date ?? now()->format('Y-m-d')
        ];
      } else {

        $expenseData = [
          'expense_name' => $request->expense_name ?? $expense->expense_name,
          'planned_amount' => $paidAmount,
          'actual_amount' => $paidBaseAmount,
          'status' => 'paid',
          'party_name' => $request->party_name ?? $expense->party_name,
          'mobile_number' => $request->mobile_number ?? $expense->mobile_number,
          'notes' => $request->notes ?? $expense->notes,
          'due_date' => $request->due_date ?? $expense->due_date,
          'is_split' => false,
          'balance_amount' => $balanceAmount,

        ];

        // Handle paid date for regular updates
        if ($request->status === 'paid') {
          $expenseData['paid_date'] = $request->paid_date ?? now()->format('Y-m-d');
        } elseif ($request->paid_date) {
          $expenseData['paid_date'] = $request->paid_date;
        } else {
          $expenseData['paid_date'] = null;
        }
      }

      // Add additional fields for standard expenses
      if ($expense->source === 'standard') {
        $expenseData['payment_mode'] = $request->payment_mode ?? $expense->payment_mode;
        $expenseData['bank_name'] = $request->bank_name ?? $expense->bank_name;
        $expenseData['upi_type'] = $request->upi_type ?? $expense->upi_type;
        $expenseData['upi_number'] = $request->upi_number ?? $expense->upi_number;
        $expenseData['party_name'] = $request->party_name ?? $expense->party_name;
        $expenseData['notes'] = $request->notes ?? $expense->notes;
        $expenseData['settle_notes'] = $request->settle_notes ?? $expense->settle_notes;
        $expenseData['frequency'] = $request->frequency ?? $expense->frequency;
        $expenseData['due_day'] = $request->due_day ?? $expense->due_day;
      }

      // Update the expense
      $expense->update($expenseData);

      // Handle GST tax
      if ($request->boolean('apply_gst')) {
        $gstData = [
          'taxable_type' => Expense::class,
          'taxable_id' => $expense->id,
          'tax_type' => 'gst',
          'tax_amount' => $gstAmountForCurrent,
          'tax_percentage' => $request->gst_percentage ?? 0,
          'payment_status' => 'received',
          'direction' => 'expense',
          'company_id' => $expense->company_id,
          'taxable_amount' => $isSplitPayment ? $paidAmount : $originalPlannedAmount

        ];

        $gstTax = null;
        if ($request->gst_tax_id && $request->gst_tax_id != 0) {
          $gstTax = Tax::find($request->gst_tax_id);
        }

        if (!$gstTax) {
          // Fallback check by type and taxable_id if ID not provided
          $gstTax = Tax::where('taxable_type', Expense::class)
            ->where('taxable_id', $expense->id)
            ->where('tax_type', 'gst')
            ->first();
        }

        if ($gstTax) {
          $gstTax->update($gstData);
        } else {
          Tax::create($gstData);
        }
      } else {
        // Remove GST tax if unchecked
        Tax::where('taxable_type', Expense::class)
          ->where('taxable_id', $expense->id)
          ->where('tax_type', 'gst')
          ->delete();
      }

      // Handle TDS tax
      if ($request->boolean('apply_tds')) {
        $tdsData = [
          'taxable_type' => Expense::class,
          'taxable_id' => $expense->id,
          'tax_type' => 'tds',
          'tax_amount' => $tdsAmountForCurrent,
          'tax_percentage' => $request->tds_percentage ?? 0,
          'payment_status' => $request->tds_status ?? 'not_received',
          'direction' => 'expense',
          'company_id' => $expense->company_id,
          'taxable_amount' => $isSplitPayment ? $paidAmount : $originalPlannedAmount

        ];

        // Add TDS dates
        if ($request->tds_due_date) {
          $tdsData['due_date'] = $request->tds_due_date;
        }

        if ($request->tds_status === 'received') {
          $tdsData['paid_date'] = now()->format('Y-m-d');
        }

        // Update or create TDS record
        $tdsTax = null;
        if ($request->tds_tax_id && $request->tds_tax_id != 0) {
          $tdsTax = Tax::find($request->tds_tax_id);
        }

        if (!$tdsTax) {
          // Fallback check by type and taxable_id if ID not provided
          $tdsTax = Tax::where('taxable_type', Expense::class)
            ->where('taxable_id', $expense->id)
            ->where('tax_type', 'tds')
            ->first();
        }

        if ($tdsTax) {
          $tdsTax->update($tdsData);
        } else {
          Tax::create($tdsData);
        }

        // Handle TDS file upload
        $tdsInputName = null;
        if ($request->hasFile('tds_file'))
          $tdsInputName = 'tds_file';
        elseif ($request->hasFile('tds_receipt'))
          $tdsInputName = 'tds_receipt';
        elseif ($request->hasFile('tds_proof'))
          $tdsInputName = 'tds_proof';

        if ($tdsInputName && $request->file($tdsInputName)->isValid()) {
          $tdsFile = $request->file($tdsInputName);
          $filename = 'tds_proof_' . $expense->id . '_' . time() . '_' . uniqid() . '.' . $tdsFile->getClientOriginalExtension();
          $path = $tdsFile->storeAs('tds_proofs', $filename, 'public');

          // Re-fetch to ensure we have the record after potential creation
          $tdsTax = Tax::where('taxable_type', Expense::class)
            ->where('taxable_id', $expense->id)
            ->where('tax_type', 'tds')
            ->first();

          if ($tdsTax) {
            $tdsTax->update([
              'tds_proof_path' => $path,
              'payment_status' => 'received',
              'paid_date' => now()
            ]);
          }
        }
      } else {
        // Remove TDS tax if unchecked
        Tax::where('taxable_type', Expense::class)
          ->where('taxable_id', $expense->id)
          ->where('tax_type', 'tds')
          ->delete();
      }

      // Create new expense for balance if this is a split payment
      $newExpenseId = null;
      if ($isSplitPayment && $balanceAmount > 0) {
        $newExpense = $expense->replicate();
        $newExpense->expense_name = $expense->expense_name;
        $newExpense->planned_amount = $balanceAmount;
        $newExpense->actual_amount = 0;
        $newExpense->original_amount = $balanceBaseAmount;
        $newExpense->status = 'pending';
        $newExpense->due_date = $request->new_due_date ?? $request->due_date ?? now()->addDays(30)->format('Y-m-d');
        $newExpense->paid_date = null;
        $newExpense->is_split = true;
        $newExpense->parent_id = $expense->id;
        $newExpense->balance_amount = $balanceAmount;
        $newExpense->schedule_amount = $balanceAmount;
        $newExpense->notes = $request->balance_notes ?? 'Balance from partial payment of expense #' . $expense->id;
        $newExpense->created_at = now();
        $newExpense->updated_at = now();
        $newExpense->save();

        $newExpenseId = $newExpense->id;

        // Create GST tax for new expense if applicable
        if ($request->boolean('apply_gst') && $originalGstAmount > 0) {
          $newGstAmount = $originalGstAmount - $gstAmountForCurrent;
          if ($newGstAmount > 0) {
            Tax::create([
              'taxable_type' => Expense::class,
              'taxable_id' => $newExpenseId,
              'tax_type' => 'gst',
              'tax_amount' => $newGstAmount,
              'tax_percentage' => $request->gst_percentage ?? 0,
              'payment_status' => 'not_received',
              'direction' => 'expense',
              'company_id' => $expense->company_id,
              'taxable_amount' => $isSplitPayment ? $balanceAmount : $originalPlannedAmount

            ]);
          }
        }

        // Create TDS tax for new expense if applicable
        if ($request->boolean('apply_tds') && $originalTdsAmount > 0) {
          $newTdsAmount = $originalTdsAmount - $tdsAmountForCurrent;
          if ($newTdsAmount > 0) {
            Tax::create([
              'taxable_type' => Expense::class,
              'taxable_id' => $newExpenseId,
              'tax_type' => 'tds',
              'tax_amount' => $newTdsAmount,
              'tax_percentage' => $request->tds_percentage ?? 0,
              'payment_status' => 'not_received',
              'direction' => 'expense',
              'company_id' => $expense->company_id,
              'taxable_amount' => $isSplitPayment ? $balanceAmount : $originalPlannedAmount

            ]);
          }
        }
      }

      // Handle new receipt uploads
      if ($request->hasFile('receipts')) {
        foreach ($request->file('receipts') as $file) {
          if ($file->isValid()) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $extension;
            $fileSize = $this->formatBytes($file->getSize());

            // Store file in public/uploads
            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
              mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $filePath = 'uploads/receipts/' . $filename;

            // Create receipt record
            Receipt::create([
              'expense_id' => $expense->id,
              'file_name' => $originalName,
              'file_path' => $filePath,
              'file_type' => $extension,
              'file_size' => $fileSize
            ]);
          }
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => $isSplitPayment ?
          'Partial payment recorded. Original expense updated and new expense created for balance.' :
          'Expense updated successfully',
        'new_expense_id' => $newExpenseId,
        'is_split_payment' => $isSplitPayment,
        'data' => [
          'original_expense' => [
            'id' => $expense->id,
            'planned_amount' => $expense->planned_amount,
            'actual_amount' => $expense->actual_amount,
            'balance_amount' => $expense->balance_amount,
            'status' => $expense->status
          ],
          'taxes' => [
            'gst_amount' => $gstAmountForCurrent,
            'tds_amount' => $tdsAmountForCurrent
          ]
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Update expense error: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error updating expense: ' . $e->getMessage()
      ], 500);
    }
  }

  private function formatBytes($bytes, $precision = 2)
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
  public function destroy($id)
  {
    $expense = Expense::findOrFail($id);

    // Prevent deleting standard auto-generated expenses
    if ($expense->type === 'standard' && $expense->source === 'standard') {
      return response()->json([
        'success' => false,
        'message' => 'Cannot delete auto-generated standard expenses'
      ], 403);
    }

    $expense->delete();

    return response()->json([
      'success' => true,
      'message' => 'Expense deleted successfully!'
    ]);
  }

  public function markAsPaid($id)
  {
    $expense = Expense::findOrFail($id);

    $expense->update([
      'actual_amount' => $expense->planned_amount,
      'balance_amount' => 0,
      'status' => 'paid',
      'paid_date' => now()->format('Y-m-d')
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Marked as paid successfully!'
    ]);
  }


  public function getReceipts($id)
  {
    try {
      $expense = Expense::with('receipts')->findOrFail($id);

      \Log::info('Getting receipts for expense ID: ' . $id);
      \Log::info('Number of receipts: ' . $expense->receipts->count());

      $receipts = $expense->receipts->map(function ($receipt) {
        \Log::info('Receipt record:');
        \Log::info('  - ID: ' . $receipt->id);
        \Log::info('  - File Path: ' . $receipt->file_path);
        \Log::info('  - File Name: ' . $receipt->file_name);


        $serverPath = public_path($receipt->file_path);
        \Log::info('  - Server Path: ' . $serverPath);


        $exists = file_exists($serverPath);
        \Log::info('  - File exists on server: ' . ($exists ? 'YES' : 'NO'));

        $fileUrl = asset('public/' . $receipt->file_path);

        return [
          'id' => $receipt->id,
          'file_name' => $receipt->file_name,
          'file_path' => $receipt->file_path,
          'file_url' => $fileUrl,
          'file_exists' => $exists,
          'file_type' => $receipt->file_type,
          'file_size' => $receipt->file_size,
          'uploaded_at' => $receipt->created_at->format('d M Y, h:i A')
        ];
      });

      return response()->json([
        'success' => true,
        'receipts' => $receipts
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in getReceipts: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error loading receipts: ' . $e->getMessage()
      ]);
    }
  }

  // Delete a receipt
  public function deleteReceipt($id)
  {
    try {
      $receipt = Receipt::findOrFail($id);

      // Delete file from storage
      if (Storage::disk('public')->exists($receipt->file_path)) {
        Storage::disk('public')->delete($receipt->file_path);
      }

      $receipt->delete();

      return response()->json([
        'success' => true,
        'message' => 'Receipt deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error deleting receipt: ' . $e->getMessage()
      ]);
    }
  }
}
