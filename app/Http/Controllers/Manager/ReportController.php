<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportExport;
use App\Exports\DetailedReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
  /**
   * Get company IDs for the authenticated user
   */
  private function getUserCompanyIds($specificCompanyId = null)
  {
    $user = auth()->user();

    if ($specificCompanyId) {
      // Verify the user has access to this specific company
      $hasAccess = Company::where('id', $specificCompanyId)
        ->where('manager_id', $user->id)
        ->exists();

      if ($hasAccess) {
        return [$specificCompanyId];
      } else {
        // If user doesn't have access, return empty array
        return [];
      }
    }

    // For regular users, only return companies they manage
    if ($user->isAdmin() || $user->isCA()) {
      return Company::where('status', 'active')->pluck('id')->toArray();
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

public function index(Request $request)
{
    // Get user's company IDs
    $companyIds = $this->getUserCompanyIds($request->company);
    
    // Get filter parameters
    $dateRange    = $request->get('date_range', 'this_month');
    $companyId    = $request->get('company', 'all');
    $outputFormat = $request->get('output', 'table');
    $startDate    = $request->get('start_date');
    $endDate      = $request->get('end_date');

    // Determine date range
    $dateFilter = $this->getDateRange($dateRange, $startDate, $endDate);
    $startDate  = $dateFilter['start_date'];
    $endDate    = $dateFilter['end_date'];

    // Get companies for filter - Use getUserCompanies() instead of Company::all()
    $companies = $this->getUserCompanies(); // ADD THIS LINE
    
    // Get all expenses for the period - Only from user's companies
    $expenseQuery = Expense::with(['company', 'categoryRelation'])
        ->whereIn('company_id', $companyIds)
        ->whereBetween('created_at', [$startDate, $endDate]);

    // Get all income for the period - Only from user's companies
    $incomeQuery = Income::with(['company'])
        ->whereIn('company_id', $companyIds)
        ->whereBetween('created_at', [$startDate, $endDate]);

    // Filter by specific company if selected
    if ($companyId && $companyId !== 'all') {
        $expenseQuery->where('company_id', $companyId);
        $incomeQuery->where('company_id', $companyId);
    }

    $expenses = $expenseQuery->get();
    $incomes = $incomeQuery->get();

    // Calculate reports
    $reports = $this->calculateReports($expenses, $incomes, $companyId);

    // Add date range label
    $reports['date_range_label'] = $dateFilter['label'];

    return view('Manager.reports', compact(
        'reports',
        'companies', // This variable is now defined
        'companyId',
        'dateRange',
        'outputFormat',
        'startDate',
        'endDate'
    ));
}

  private function getDateRange($range, $customStart = null, $customEnd = null)
  {
    $today = Carbon::today();

    switch ($range) {
      case 'today':
        $startDate = $today->copy();
        $endDate = $today->copy()->endOfDay();
        $label = 'Today';
        break;

      case 'this_week':
        $startDate = $today->copy()->startOfWeek();
        $endDate = $today->copy()->endOfWeek();
        $label = 'This Week';
        break;

      case 'this_month':
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()->endOfMonth();
        $label = 'This Month';
        break;

      case 'this_quarter':
        $startDate = $today->copy()->startOfQuarter();
        $endDate = $today->copy()->endOfQuarter();
        $label = 'This Quarter';
        break;

      case 'this_year':
        $startDate = $today->copy()->startOfYear();
        $endDate = $today->copy()->endOfYear();
        $label = 'This Year';
        break;

      case 'custom':
        $startDate = $customStart ? Carbon::parse($customStart)->startOfDay() : $today->copy()->startOfMonth();
        $endDate = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $today->copy()->endOfMonth();
        $label = 'Custom Range';
        break;

      default:
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()->endOfMonth();
        $label = 'This Month';
    }

    return [
      'start_date' => $startDate,
      'end_date'   => $endDate,
      'label'      => $label
    ];
  }

  private function calculateReports($expenses, $incomes, $companyFilter)
  {
    // Initialize report data
    $reports = [
      'summary'               => [
        'total_income'  => 0,
        'total_expense' => 0,
        'net_profit'    => 0,
        'expense_count' => 0,
        'income_count'  => 0,
        'gst_collected' => 0,
        'tds_collected' => 0,
        'gst_paid'      => 0,
        'tds_paid'      => 0,
      ],
      'company_wise'          => [],
      'category_wise'         => [],
      'upcoming_payments'     => [],
      'non_standard_expenses' => [],
      'monthly_trend'         => $this->calculateMonthlyTrend(),
      'top_expenses'          => [],
      'recent_transactions'   => []
    ];

    // Calculate income totals
    foreach ($incomes as $income) {
      $reports['summary']['total_income'] += $income->amount ?? $income->planned_amount ?? 0;
      $reports['summary']['income_count']++;

      // Add GST/TDS from income
      $reports['summary']['gst_collected'] += $income->gst_amount ?? 0;
      $reports['summary']['tds_collected'] += $income->tds_amount ?? 0;
    }

    // Calculate expense totals
    foreach ($expenses as $expense) {
      $amount = $expense->actual_amount ?? $expense->planned_amount ?? 0;

      $reports['summary']['total_expense'] += $amount;
      $reports['summary']['expense_count']++;

      // Add GST/TDS from expenses
      $reports['summary']['gst_paid'] += $expense->gst_amount ?? 0;
      $reports['summary']['tds_paid'] += $expense->tds_amount ?? 0;

      // Company-wise breakdown
      $companyName = $expense->company ? $expense->company->name : 'No Company';
      if (!isset($reports['company_wise'][$companyName])) {
        $reports['company_wise'][$companyName] = [
          'income'  => 0,
          'expense' => 0,
          'profit'  => 0,
          'company' => $expense->company
        ];
      }
      $reports['company_wise'][$companyName]['expense'] += $amount;

      // Category-wise breakdown
      $categoryName = $expense->categoryRelation ? $expense->categoryRelation->name : 'Uncategorized';
      if (!isset($reports['category_wise'][$categoryName])) {
        $reports['category_wise'][$categoryName] = [
          'amount'   => 0,
          'count'    => 0,
          'category' => $expense->categoryRelation
        ];
      }
      $reports['category_wise'][$categoryName]['amount'] += $amount;
      $reports['category_wise'][$categoryName]['count']++;

      // Upcoming payments (expenses with upcoming status)
      if ($expense->status === 'upcoming' || $expense->status === 'pending') {
        $reports['upcoming_payments'][] = [
          'expense'   => $expense,
          'amount'    => $amount,
          'due_date'  => $expense->due_date,
          'frequency' => $expense->frequency
        ];
      }

      // Non-standard expenses
      if ($expense->source === 'manual' || $expense->source === 'non-standard') {
        $reports['non_standard_expenses'][] = [
          'expense'    => $expense,
          'amount'     => $amount,
          'party_name' => $expense->party_name,
          'date'       => $expense->created_at
        ];
      }

      // Top expenses (by amount)
      $reports['top_expenses'][] = [
        'name'   => $expense->expense_name,
        'amount' => $amount,
        'date'   => $expense->created_at,
        'company' => $expense->company ? $expense->company->name : 'N/A'
      ];
    }

    // Add income to company-wise breakdown
    foreach ($incomes as $income) {
      $companyName = $income->company ? $income->company->name : 'No Company';
      if (!isset($reports['company_wise'][$companyName])) {
        $reports['company_wise'][$companyName] = [
          'income'  => 0,
          'expense' => 0,
          'profit'  => 0,
          'company' => $income->company
        ];
      }
      $incomeAmount = $income->amount ?? $income->planned_amount ?? 0;
      $reports['company_wise'][$companyName]['income'] += $incomeAmount;
    }

    // Calculate profit for each company
    foreach ($reports['company_wise'] as &$companyData) {
      $companyData['profit'] = $companyData['income'] - $companyData['expense'];
    }

    // Calculate net profit
    $reports['summary']['net_profit'] =
      $reports['summary']['total_income'] - $reports['summary']['total_expense'];

    // Sort company-wise by profit
    uasort($reports['company_wise'], function ($a, $b) {
      return $b['profit'] <=> $a['profit'];
    });

    // Sort category-wise by amount
    arsort($reports['category_wise']);

    // Sort top expenses by amount
    usort($reports['top_expenses'], function ($a, $b) {
      return $b['amount'] <=> $a['amount'];
    });
    $reports['top_expenses'] = array_slice($reports['top_expenses'], 0, 10);

    // Get recent transactions (last 10)
    $recentTransactions = array_merge(
      $expenses->map(function ($expense) {
        return [
          'type'      => 'Expense',
          'name'      => $expense->expense_name,
          'amount'    => $expense->actual_amount ?? $expense->planned_amount ?? 0,
          'date'      => $expense->created_at,
          'company'   => $expense->company ? $expense->company->name : 'N/A',
          'status'    => $expense->status
        ];
      })->toArray(),
      $incomes->map(function ($income) {
        return [
          'type'      => 'Income',
          'name'      => $income->description ?? 'Income',
          'amount'    => $income->amount ?? $income->planned_amount ?? 0,
          'date'      => $income->created_at,
          'company'   => $income->company ? $income->company->name : 'N/A',
          'status'    => $income->status
        ];
      })->toArray()
    );

    usort($recentTransactions, function ($a, $b) {
      return strtotime($b['date']) <=> strtotime($a['date']);
    });

    $reports['recent_transactions'] = array_slice($recentTransactions, 0, 10);

    return $reports;
  }

  private function calculateMonthlyTrend()
  {
    $user = auth()->user();
    $companyIds = $this->getUserCompanyIds();

    $trend = [];
    $today = Carbon::today();

    // Get data for last 6 months
    for ($i = 5; $i >= 0; $i--) {
      $month = $today->copy()->subMonths($i);
      $startOfMonth = $month->copy()->startOfMonth();
      $endOfMonth = $month->copy()->endOfMonth();

      $income = Income::whereIn('company_id', $companyIds)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('amount');

      $expense = Expense::whereIn('company_id', $companyIds)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->sum('actual_amount');

      $trend[] = [
        'month'   => $month->format('M Y'),
        'income'  => $income ?? 0,
        'expense' => $expense ?? 0,
        'profit'  => ($income ?? 0) - ($expense ?? 0)
      ];
    }

    return $trend;
  }

  public function exportExcel(Request $request)
  {
    // Get user's company IDs
    $companyIds = $this->getUserCompanyIds($request->company);

    // Get filter parameters
    $dateRange    = $request->get('date_range', 'this_month');
    $companyId    = $request->get('company', 'all');
    $reportType   = $request->get('report_type', 'summary');
    $startDate    = $request->get('start_date');
    $endDate      = $request->get('end_date');

    // Determine date range
    $dateFilter = $this->getDateRange($dateRange, $startDate, $endDate);
    $startDate  = $dateFilter['start_date'];
    $endDate    = $dateFilter['end_date'];

    // Prepare data based on report type
    if ($reportType === 'summary') {
      return $this->exportSummaryExcel($companyIds, $companyId, $startDate, $endDate, $dateFilter['label']);
    } elseif ($reportType === 'detailed') {
      return $this->exportDetailedExcel($companyIds, $companyId, $startDate, $endDate, $dateFilter['label']);
    } elseif ($reportType === 'company_wise') {
      return $this->exportCompanyWiseExcel($companyIds, $companyId, $startDate, $endDate, $dateFilter['label']);
    }

    // Default to summary report
    return $this->exportSummaryExcel($companyIds, $companyId, $startDate, $endDate, $dateFilter['label']);
  }

  private function exportSummaryExcel($companyIds, $companyId, $startDate, $endDate, $periodLabel)
  {
    // Get data
    $expenseQuery = Expense::whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    $incomeQuery = Income::whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    if ($companyId && $companyId !== 'all') {
      $expenseQuery->where('company_id', $companyId);
      $incomeQuery->where('company_id', $companyId);
    }

    $expenses = $expenseQuery->get();
    $incomes = $incomeQuery->get();

    // Calculate summary
    $totalIncome = $incomes->sum('amount') ?? 0;
    $totalExpense = $expenses->sum('actual_amount') ?? 0;
    $netProfit = $totalIncome - $totalExpense;

    $fileName = 'financial_report_' . date('Y_m_d') . '.xlsx';

    return Excel::download(new ReportExport([
      'period_label' => $periodLabel,
      'start_date' => $startDate->format('Y-m-d'),
      'end_date' => $endDate->format('Y-m-d'),
      'total_income' => $totalIncome,
      'total_expense' => $totalExpense,
      'net_profit' => $netProfit,
      'income_count' => $incomes->count(),
      'expense_count' => $expenses->count(),
      'incomes' => $incomes,
      'expenses' => $expenses
    ]), $fileName);
  }

  private function exportDetailedExcel($companyIds, $companyId, $startDate, $endDate, $periodLabel)
  {
    $expenseQuery = Expense::with(['company', 'categoryRelation'])
      ->whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    $incomeQuery = Income::with(['company'])
      ->whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    if ($companyId && $companyId !== 'all') {
      $expenseQuery->where('company_id', $companyId);
      $incomeQuery->where('company_id', $companyId);
    }

    $expenses = $expenseQuery->get();
    $incomes = $incomeQuery->get();

    $fileName = 'detailed_financial_report_' . date('Y_m_d') . '.xlsx';

    return Excel::download(new DetailedReportExport([
      'period_label' => $periodLabel,
      'start_date' => $startDate->format('Y-m-d'),
      'end_date' => $endDate->format('Y-m-d'),
      'expenses' => $expenses,
      'incomes' => $incomes
    ]), $fileName);
  }

  private function exportCompanyWiseExcel($companyIds, $companyId, $startDate, $endDate, $periodLabel)
  {
    $companies = Company::whereIn('id', $companyIds)->get();

    $companyData = [];
    foreach ($companies as $company) {
      $income = Income::where('company_id', $company->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('amount');

      $expense = Expense::where('company_id', $company->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('actual_amount');

      $profit = $income - $expense;

      $companyData[] = [
        'company_name' => $company->name,
        'income' => $income ?? 0,
        'expense' => $expense ?? 0,
        'profit' => $profit,
        'margin' => $income > 0 ? ($profit / $income) * 100 : 0
      ];
    }

    $fileName = 'company_wise_report_' . date('Y_m_d') . '.xlsx';

    return Excel::download(new ReportExport([
      'period_label' => $periodLabel,
      'start_date' => $startDate->format('Y-m-d'),
      'end_date' => $endDate->format('Y-m-d'),
      'company_data' => $companyData
    ], 'company_wise'), $fileName);
  }

  public function exportPdf(Request $request)
  {
    // Get user's company IDs
    $companyIds = $this->getUserCompanyIds($request->company);

    // Get filter parameters
    $dateRange    = $request->get('date_range', 'this_month');
    $companyId    = $request->get('company', 'all');
    $reportType   = $request->get('report_type', 'summary');
    $startDate    = $request->get('start_date');
    $endDate      = $request->get('end_date');

    // Determine date range
    $dateFilter = $this->getDateRange($dateRange, $startDate, $endDate);
    $startDate  = $dateFilter['start_date'];
    $endDate    = $dateFilter['end_date'];

    // Get data
    $expenseQuery = Expense::with(['company', 'categoryRelation'])
      ->whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    $incomeQuery = Income::with(['company'])
      ->whereIn('company_id', $companyIds)
      ->whereBetween('created_at', [$startDate, $endDate]);

    if ($companyId && $companyId !== 'all') {
      $expenseQuery->where('company_id', $companyId);
      $incomeQuery->where('company_id', $companyId);
    }

    $expenses = $expenseQuery->get();
    $incomes = $incomeQuery->get();

    // Calculate totals
    $totalIncome = $incomes->sum('amount') ?? 0;
    $totalExpense = $expenses->sum('actual_amount') ?? 0;
    $netProfit = $totalIncome - $totalExpense;

    // Get company name if filtered
    $companyName = 'All Companies';
    if ($companyId && $companyId !== 'all') {
      $company = Company::find($companyId);
      $companyName = $company ? $company->name : 'Selected Company';
    }

    // Get user info
    $user = auth()->user();

    $pdf = Pdf::loadView('Manager.exports.report_pdf', [
      'report_type' => $reportType,
      'period_label' => $dateFilter['label'],
      'start_date' => $startDate->format('Y-m-d'),
      'end_date' => $endDate->format('Y-m-d'),
      'company_name' => $companyName,
      'total_income' => $totalIncome,
      'total_expense' => $totalExpense,
      'net_profit' => $netProfit,
      'income_count' => $incomes->count(),
      'expense_count' => $expenses->count(),
      'incomes' => $incomes,
      'expenses' => $expenses,
      'generated_by' => $user->name,
      'generated_at' => now()->format('Y-m-d H:i:s')
    ]);

    $fileName = 'financial_report_' . date('Y_m_d') . '.pdf';

    return $pdf->download($fileName);
  }

  public function exportCustom(Request $request)
  {
    $request->validate([
      'report_type' => 'required|in:summary,detailed,company_wise',
      'date_range' => 'required|in:today,this_week,this_month,this_quarter,this_year,custom',
      'company' => 'nullable|exists:companies,id',
      'start_date' => 'required_if:date_range,custom|date',
      'end_date' => 'required_if:date_range,custom|date',
      'export_format' => 'required|in:excel,pdf'
    ]);

    // Check company access
    if ($request->company) {
      $userCompanyIds = $this->getUserCompanyIds();
      if (!in_array($request->company, $userCompanyIds)) {
        return back()->with('error', 'You do not have access to this company');
      }
    }

    if ($request->export_format === 'excel') {
      return $this->exportExcel($request);
    } else {
      return $this->exportPdf($request);
    }
  }
}
