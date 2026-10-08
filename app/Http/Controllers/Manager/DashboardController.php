<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\{Company, Expense, Income, UpcomingPayment};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filters
        $dateRange = $request->get('range', 'month');
        $companyId = $request->get('company');
        $startDateParam = $request->get('start_date');
        $endDateParam = $request->get('end_date');
        $viewType = $request->get('view', 'summary');
        
        // Get date range
        $dateFilters = $this->getDateRange($dateRange, $startDateParam, $endDateParam);
        $startDate = $dateFilters['start'];
        $endDate = $dateFilters['end'];
        
        // Get company IDs accessible to the manager
        $companyIds = $this->getCompanyIds($user, $companyId);
        
        // Calculate current period stats
        $currentStats = $this->calculateCurrentStats($user, $companyIds, $startDate, $endDate, $dateRange);
        
        // Calculate previous period stats (for comparison)
        $diffDays = $startDate->diffInDays($endDate) ?: 30;
        $previousStartDate = (clone $startDate)->subDays($diffDays + 1);
        $previousEndDate = (clone $startDate)->subDay()->endOfDay();
        $previousStats = $this->calculatePreviousStats($user, $companyIds, $previousStartDate, $previousEndDate);
        
        // Get immediate payments / due expenses
        $immediatePayments = $this->getImmediatePaymentsData($user, $companyIds);
        
        // Get company profit/loss performance
        $companyProfitLoss = $this->getCompanyProfitLossData($user, $companyIds, $startDate, $endDate);
        
        // Get financial trend timeline data (income vs expense over time)
        $trendData = $this->getFinancialTrendData($user, $companyIds, $startDate, $endDate, $dateRange);
        
        // Get expense category breakdown
        $categoryBreakdown = $this->getCategoryBreakdownData($user, $companyIds, $startDate, $endDate);
        
        // Get notifications
        $notifications = $this->getNotificationsData($user, $companyIds);
        
        // Get recent transactions
        $recentTransactions = $this->getRecentTransactions($user, $companyIds);
        
        // Get companies for dropdown
        $companies = $this->getCompaniesForDropdown($user);
        
        // Selected company object if filtered
        $selectedCompany = $companyId ? Company::find($companyId) : null;
        
        return view('Manager.dashboard', compact(
            'currentStats',
            'previousStats',
            'immediatePayments',
            'companyProfitLoss',
            'trendData',
            'categoryBreakdown',
            'recentTransactions',
            'notifications',
            'companies',
            'selectedCompany',
            'dateRange',
            'startDateParam',
            'endDateParam',
            'companyId',
            'viewType'
        ));
    }

    private function getCompanyIds($user, $specificCompanyId = null)
    {
        if ($specificCompanyId) {
            if ($user->isAdmin() || $user->isCA()) {
                $hasAccess = Company::where('id', $specificCompanyId)->exists();
            } else {
                $hasAccess = Company::forManager($user)
                    ->where('id', $specificCompanyId)
                    ->exists();
            }
            
            return $hasAccess ? [$specificCompanyId] : [];
        }

        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->pluck('id')->toArray();
        }

        return Company::forManager($user)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    private function calculateCurrentStats($user, $companyIds, $startDate, $endDate, $dateRange)
    {
        if (empty($companyIds)) {
            return [
                'totalIncome' => 0,
                'totalExpenses' => 0,
                'netProfit' => 0,
                'upcomingPayments' => 0,
                'periodLabel' => $this->getPeriodLabel($dateRange),
                'totalReceivableIncome' => 0,
                'totalPayableExpenses' => 0,
                'paidExpensesCount' => 0,
                'receivedIncomeCount' => 0,
            ];
        }

        $incomeQuery = Income::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalIncome = (clone $incomeQuery)
            ->where('status', 'received')
            ->sum('amount') ?: 0;

        $receivedIncomeCount = (clone $incomeQuery)
            ->where('status', 'received')
            ->count();

        $totalReceivableIncome = (clone $incomeQuery)
            ->whereIn('status', ['pending', 'upcoming', 'overdue', 'partially_paid'])
            ->sum('amount') ?: 0;

        $expenseQuery = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalExpenses = (clone $expenseQuery)
            ->where('status', 'paid')
            ->sum('planned_amount') ?: 0;

        $paidExpensesCount = (clone $expenseQuery)
            ->where('status', 'paid')
            ->count();

        $totalPayableExpenses = (clone $expenseQuery)
            ->whereIn('status', ['pending', 'upcoming', 'overdue', 'partially_paid'])
            ->sum('planned_amount') ?: 0;

        $upcomingPayments = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereIn('status', ['upcoming', 'pending'])
            ->whereBetween('due_date', [Carbon::now()->startOfDay(), Carbon::now()->addDays(7)->endOfDay()])
            ->sum('planned_amount') ?: 0;

        return [
            'totalIncome' => (float)$totalIncome,
            'totalExpenses' => (float)$totalExpenses,
            'netProfit' => (float)($totalIncome - $totalExpenses),
            'upcomingPayments' => (float)$upcomingPayments,
            'periodLabel' => $this->getPeriodLabel($dateRange),
            'totalReceivableIncome' => (float)$totalReceivableIncome,
            'totalPayableExpenses' => (float)$totalPayableExpenses,
            'paidExpensesCount' => $paidExpensesCount,
            'receivedIncomeCount' => $receivedIncomeCount,
        ];
    }

    private function calculatePreviousStats($user, $companyIds, $startDate, $endDate)
    {
        if (empty($companyIds)) {
            return [
                'totalIncome' => 0,
                'totalExpenses' => 0,
                'netProfit' => 0,
                'totalReceivableIncome' => 0,
                'totalPayableExpenses' => 0,
            ];
        }

        $incomeQuery = Income::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalIncome = (clone $incomeQuery)
            ->where('status', 'received')
            ->sum('amount') ?: 0;

        $totalReceivableIncome = (clone $incomeQuery)
            ->whereIn('status', ['pending', 'upcoming', 'overdue', 'partially_paid'])
            ->sum('amount') ?: 0;

        $expenseQuery = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalExpenses = (clone $expenseQuery)
            ->where('status', 'paid')
            ->sum('planned_amount') ?: 0;

        $totalPayableExpenses = (clone $expenseQuery)
            ->whereIn('status', ['pending', 'upcoming', 'overdue', 'partially_paid'])
            ->sum('planned_amount') ?: 0;

        return [
            'totalIncome' => (float)$totalIncome,
            'totalExpenses' => (float)$totalExpenses,
            'netProfit' => (float)($totalIncome - $totalExpenses),
            'totalReceivableIncome' => (float)$totalReceivableIncome,
            'totalPayableExpenses' => (float)$totalPayableExpenses,
        ];
    }

    private function getImmediatePaymentsData($user, $companyIds)
    {
        if (empty($companyIds)) {
            return collect([]);
        }

        $expenses = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereIn('status', ['upcoming', 'pending', 'overdue'])
            ->where('due_date', '<=', Carbon::now()->addDays(7)->endOfDay())
            ->with(['company', 'categoryRelation'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        return $expenses->map(function ($expense) {
            $isOverdue = $expense->due_date && Carbon::parse($expense->due_date)->lt(Carbon::today());
            $status = $isOverdue ? 'overdue' : ($expense->status ?? 'pending');

            return [
                'id' => $expense->id,
                'date' => $expense->due_date ? Carbon::parse($expense->due_date)->format('d M Y') : 'N/A',
                'raw_date' => $expense->due_date ? Carbon::parse($expense->due_date)->format('Y-m-d') : '',
                'company' => $expense->company->name ?? 'N/A',
                'name' => $expense->expense_name ?: ($expense->categoryRelation->name ?? 'Expense #' . $expense->id),
                'type' => ucfirst($expense->source ?? 'Standard'),
                'party' => $expense->party_name ?? 'N/A',
                'amount' => (float)($expense->planned_amount ?? 0),
                'status' => $status,
                'is_standard' => $expense->source === 'standard'
            ];
        });
    }

    private function getCompanyProfitLossData($user, $companyIds, $startDate, $endDate)
    {
        if (empty($companyIds)) {
            return collect([]);
        }

        return Company::whereIn('id', $companyIds)
            ->get()
            ->map(function ($company) use ($user, $startDate, $endDate) {
                $income = Income::where('company_id', $company->id)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'received')
                    ->sum('amount') ?: 0;
                
                $expenses = Expense::where('company_id', $company->id)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'paid')
                    ->sum('planned_amount') ?: 0;

                $pendingExpense = Expense::where('company_id', $company->id)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['pending', 'upcoming', 'overdue'])
                    ->sum('planned_amount') ?: 0;

                $profit = $income - $expenses;
                $margin = $income > 0 ? round(($profit / $income) * 100, 1) : 0;

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'income' => (float)$income,
                    'expenses' => (float)$expenses,
                    'pending_expense' => (float)$pendingExpense,
                    'profit' => (float)$profit,
                    'margin' => $margin,
                ];
            });
    }

    private function getFinancialTrendData($user, $companyIds, $startDate, $endDate, $dateRange)
    {
        if (empty($companyIds)) {
            return [
                'labels' => [],
                'income' => [],
                'expenses' => [],
                'profit' => []
            ];
        }

        $labels = [];
        $incomeData = [];
        $expenseData = [];
        $profitData = [];

        if ($dateRange === 'today') {
            // Group by 4-hour slots
            $slots = [
                '00:00 - 04:00' => [Carbon::today()->startOfDay(), Carbon::today()->startOfDay()->addHours(4)],
                '04:00 - 08:00' => [Carbon::today()->startOfDay()->addHours(4), Carbon::today()->startOfDay()->addHours(8)],
                '08:00 - 12:00' => [Carbon::today()->startOfDay()->addHours(8), Carbon::today()->startOfDay()->addHours(12)],
                '12:00 - 16:00' => [Carbon::today()->startOfDay()->addHours(12), Carbon::today()->startOfDay()->addHours(16)],
                '16:00 - 20:00' => [Carbon::today()->startOfDay()->addHours(16), Carbon::today()->startOfDay()->addHours(20)],
                '20:00 - 24:00' => [Carbon::today()->startOfDay()->addHours(20), Carbon::today()->endOfDay()],
            ];

            foreach ($slots as $label => [$slotStart, $slotEnd]) {
                $inc = Income::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$slotStart, $slotEnd])
                    ->where('status', 'received')
                    ->sum('amount') ?: 0;

                $exp = Expense::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$slotStart, $slotEnd])
                    ->where('status', 'paid')
                    ->sum('planned_amount') ?: 0;

                $labels[] = $label;
                $incomeData[] = (float)$inc;
                $expenseData[] = (float)$exp;
                $profitData[] = (float)($inc - $exp);
            }
        } elseif ($dateRange === 'week') {
            // Group by each day of the week (7 days)
            $curr = (clone $startDate)->startOfDay();
            while ($curr->lte($endDate)) {
                $dayStart = (clone $curr)->startOfDay();
                $dayEnd = (clone $curr)->endOfDay();
                $labels[] = $curr->format('D, d M');

                $inc = Income::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('status', 'received')
                    ->sum('amount') ?: 0;

                $exp = Expense::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('status', 'paid')
                    ->sum('planned_amount') ?: 0;

                $incomeData[] = (float)$inc;
                $expenseData[] = (float)$exp;
                $profitData[] = (float)($inc - $exp);

                $curr->addDay();
            }
        } elseif ($dateRange === 'month') {
            // Group into weekly buckets (Week 1, Week 2, Week 3, Week 4, etc.)
            $curr = (clone $startDate)->startOfDay();
            $weekNum = 1;
            while ($curr->lte($endDate)) {
                $weekStart = (clone $curr);
                $weekEnd = (clone $curr)->addDays(6)->endOfDay();
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = (clone $endDate);
                }

                $labels[] = "W{$weekNum} (" . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M') . ')';

                $inc = Income::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where('status', 'received')
                    ->sum('amount') ?: 0;

                $exp = Expense::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where('status', 'paid')
                    ->sum('planned_amount') ?: 0;

                $incomeData[] = (float)$inc;
                $expenseData[] = (float)$exp;
                $profitData[] = (float)($inc - $exp);

                $curr->addDays(7);
                $weekNum++;
            }
        } elseif ($dateRange === 'quarter' || $dateRange === 'year' || $dateRange === 'all') {
            // Group by month
            $curr = (clone $startDate)->startOfMonth();
            while ($curr->lte($endDate)) {
                $mStart = (clone $curr)->startOfMonth();
                $mEnd = (clone $curr)->endOfMonth();
                if ($mEnd->gt($endDate)) {
                    $mEnd = (clone $endDate);
                }
                $labels[] = $curr->format('M Y');

                $inc = Income::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$mStart, $mEnd])
                    ->where('status', 'received')
                    ->sum('amount') ?: 0;

                $exp = Expense::whereIn('company_id', $companyIds)
                    ->visibleToUser($user)
                    ->whereBetween('created_at', [$mStart, $mEnd])
                    ->where('status', 'paid')
                    ->sum('planned_amount') ?: 0;

                $incomeData[] = (float)$inc;
                $expenseData[] = (float)$exp;
                $profitData[] = (float)($inc - $exp);

                $curr->addMonth();
            }
        } else {
            // Custom date range
            $diffDays = $startDate->diffInDays($endDate);
            if ($diffDays <= 14) {
                // Group by day
                $curr = (clone $startDate)->startOfDay();
                while ($curr->lte($endDate)) {
                    $labels[] = $curr->format('d M');
                    $dayStart = (clone $curr)->startOfDay();
                    $dayEnd = (clone $curr)->endOfDay();

                    $inc = Income::whereIn('company_id', $companyIds)
                        ->visibleToUser($user)
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->where('status', 'received')
                        ->sum('amount') ?: 0;

                    $exp = Expense::whereIn('company_id', $companyIds)
                        ->visibleToUser($user)
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->where('status', 'paid')
                        ->sum('planned_amount') ?: 0;

                    $incomeData[] = (float)$inc;
                    $expenseData[] = (float)$exp;
                    $profitData[] = (float)($inc - $exp);

                    $curr->addDay();
                }
            } else {
                // Group by month
                $curr = (clone $startDate)->startOfMonth();
                while ($curr->lte($endDate)) {
                    $labels[] = $curr->format('M Y');
                    $mStart = (clone $curr)->startOfMonth();
                    $mEnd = (clone $curr)->endOfMonth();

                    $inc = Income::whereIn('company_id', $companyIds)
                        ->visibleToUser($user)
                        ->whereBetween('created_at', [$mStart, $mEnd])
                        ->where('status', 'received')
                        ->sum('amount') ?: 0;

                    $exp = Expense::whereIn('company_id', $companyIds)
                        ->visibleToUser($user)
                        ->whereBetween('created_at', [$mStart, $mEnd])
                        ->where('status', 'paid')
                        ->sum('planned_amount') ?: 0;

                    $incomeData[] = (float)$inc;
                    $expenseData[] = (float)$exp;
                    $profitData[] = (float)($inc - $exp);

                    $curr->addMonth();
                }
            }
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
            'profit' => $profitData
        ];
    }

    private function getCategoryBreakdownData($user, $companyIds, $startDate, $endDate)
    {
        if (empty($companyIds)) {
            return [
                'labels' => [],
                'data' => [],
                'colors' => []
            ];
        }

        $expenses = Expense::whereIn('expenses.company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('expenses.created_at', [$startDate, $endDate])
            ->where('expenses.status', 'paid')
            ->leftJoin('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as cat_name, SUM(expenses.planned_amount) as total')
            ->groupBy('cat_name')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $palette = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#ec4899', '#64748b'];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($expenses as $idx => $item) {
            $labels[] = $item->cat_name;
            $data[] = (float)$item->total;
            $colors[] = $palette[$idx % count($palette)];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors
        ];
    }

    private function getRecentTransactions($user, $companyIds)
    {
        if (empty($companyIds)) {
            return collect([]);
        }

        $incomes = Income::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->with('company')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'income',
                    'title' => $item->description ?: ($item->party_name ?? 'Income Entry'),
                    'party' => $item->party_name ?? 'Client',
                    'company' => $item->company->name ?? 'N/A',
                    'amount' => (float)$item->amount,
                    'status' => $item->status ?? 'received',
                    'date' => $item->created_at ? $item->created_at->format('d M, h:i A') : 'N/A',
                    'created_at' => $item->created_at
                ];
            });

        $expenses = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->with(['company', 'categoryRelation'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'expense',
                    'title' => $item->expense_name ?: ($item->categoryRelation->name ?? 'Expense Entry'),
                    'party' => $item->party_name ?? 'Vendor',
                    'company' => $item->company->name ?? 'N/A',
                    'amount' => (float)($item->planned_amount ?? $item->actual_amount),
                    'status' => $item->status ?? 'paid',
                    'date' => $item->created_at ? $item->created_at->format('d M, h:i A') : 'N/A',
                    'created_at' => $item->created_at
                ];
            });

        return $incomes->concat($expenses)->sortByDesc('created_at')->take(6)->values();
    }

    private function getNotificationsData($user, $companyIds)
    {
        if (empty($companyIds)) {
            return [];
        }

        $notifications = [];

        // Overdue expenses
        $overdueCount = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->where('due_date', '<', Carbon::today())
            ->whereIn('status', ['pending', 'upcoming', 'overdue'])
            ->count();

        if ($overdueCount > 0) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'warning',
                'message' => "You have {$overdueCount} overdue expense payments requiring attention",
                'link' => route('manager.expenses', ['status' => 'overdue'])
            ];
        }

        // Upcoming payments due in next 7 days
        $upcomingCount = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->whereIn('status', ['pending', 'upcoming'])
            ->count();

        if ($upcomingCount > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'event',
                'message' => "You have {$upcomingCount} payments scheduled in the next 7 days",
                'link' => route('manager.expenses', ['status' => 'upcoming'])
            ];
        }

        // Pending approvals or pending expenses
        $pendingExpenseCount = Expense::whereIn('company_id', $companyIds)
            ->visibleToUser($user)
            ->where('status', 'pending')
            ->count();

        if ($pendingExpenseCount > 0) {
            $notifications[] = [
                'type' => 'info',
                'icon' => 'receipt_long',
                'message' => "{$pendingExpenseCount} pending expenses waiting to be settled",
                'link' => route('manager.expenses', ['status' => 'pending'])
            ];
        }

        // Add default status if clean
        if (empty($notifications)) {
            $notifications[] = [
                'type' => 'success',
                'icon' => 'verified',
                'message' => "All scheduled company payments and invoices are in good standing",
                'link' => '#'
            ];
        }

        return $notifications;
    }

    private function getCompaniesForDropdown($user)
    {
        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        }

        return Company::forManager($user)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function getDateRange($range, $customStart = null, $customEnd = null)
    {
        if ($range === 'custom' && $customStart && $customEnd) {
            return [
                'start' => Carbon::parse($customStart)->startOfDay(),
                'end' => Carbon::parse($customEnd)->endOfDay()
            ];
        }

        switch ($range) {
            case 'today':
                return [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek()->startOfDay(),
                    'end' => Carbon::now()->endOfWeek()->endOfDay()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end' => Carbon::now()->endOfMonth()->endOfDay()
                ];
            case 'quarter':
                return [
                    'start' => Carbon::now()->startOfQuarter()->startOfDay(),
                    'end' => Carbon::now()->endOfQuarter()->endOfDay()
                ];
            case 'year':
                return [
                    'start' => Carbon::now()->startOfYear()->startOfDay(),
                    'end' => Carbon::now()->endOfYear()->endOfDay()
                ];
            case 'all':
                return [
                    'start' => Carbon::now()->subYears(5)->startOfYear(),
                    'end' => Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'start' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end' => Carbon::now()->endOfMonth()->endOfDay()
                ];
        }
    }

    private function getPeriodLabel($range)
    {
        $labels = [
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'quarter' => 'This Quarter',
            'year' => 'This Year',
            'all' => 'All Time',
            'custom' => 'Custom Period'
        ];

        return $labels[$range] ?? 'This Month';
    }
}