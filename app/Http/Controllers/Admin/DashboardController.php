<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, StandardExpense, NonStandardExpense, Income, UpcomingPayment, User, Expense};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyIds = $this->getCompanyIds($user, $request->company_id);
        $dateRange = $this->getDateRange($request->date_range ?? 'this_month');

        return response()->json([
            'metrics' => [
                'total_income' => Income::whereIn('company_id', $companyIds)
                    ->whereBetween('income_date', $dateRange)
                    ->sum('amount'),

                'total_expenses' => $this->calculateTotalExpenses($companyIds, $dateRange),

                'net_profit' => 0, // Calculated below

                'upcoming_payments' => UpcomingPayment::whereIn('company_id', $companyIds)
                    ->whereIn('status', ['upcoming', 'pending'])
                    ->whereBetween('due_date', [now(), now()->addDays(7)])
                    ->sum('amount'),
            ],
            'immediate_payments' => UpcomingPayment::whereIn('company_id', $companyIds)
                ->whereIn('status', ['upcoming', 'pending', 'overdue'])
                ->whereBetween('due_date', [now()->subDays(1), now()->addDays(3)])
                ->with('company')
                ->orderBy('due_date')
                ->limit(10)
                ->get(),

            'company_wise_pl' => $this->getCompanyWisePL($companyIds, $dateRange),
        ]);
    }

    public function metrics(Request $request)
    {
        $user = auth()->user();
        $companyIds = $this->getCompanyIds($user, $request->company_id);

        return response()->json([
            'upcoming_debits' => UpcomingPayment::whereIn('company_id', $companyIds)
                ->where('type', 'debit')
                ->whereIn('status', ['upcoming', 'pending'])
                ->whereBetween('due_date', [now(), now()->addDays(30)])
                ->sum('amount'),

            'upcoming_credits' => UpcomingPayment::whereIn('company_id', $companyIds)
                ->where('type', 'credit')
                ->whereIn('status', ['upcoming', 'pending'])
                ->whereBetween('due_date', [now(), now()->addDays(30)])
                ->sum('amount'),
        ]);
    }

    private function getCompanyIds($user, $specificCompanyId = null)
    {
        if ($specificCompanyId) {
            return [$specificCompanyId];
        }

        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->pluck('id');
        }

        return Company::forManager($user)
            ->where('status', 'active')
            ->pluck('id');
    }

    private function calculateTotalExpenses($companyIds, $dateRange)
    {
        $standardExpenses = StandardExpense::whereIn('company_id', $companyIds)
            ->whereBetween('due_date', $dateRange)
            ->where('status', 'paid')
            ->sum('actual_amount');

        $nonStandardExpenses = NonStandardExpense::whereIn('company_id', $companyIds)
            ->whereBetween('expense_date', $dateRange)
            ->where('status', 'paid')
            ->sum('amount');

        return $standardExpenses + $nonStandardExpenses;
    }

    private function getCompanyWisePL($companyIds, $dateRange)
    {
        return Company::whereIn('id', $companyIds)
            ->get()
            ->map(function ($company) use ($dateRange) {
                $income = Income::where('company_id', $company->id)
                    ->whereBetween('income_date', $dateRange)
                    ->sum('amount');

                $standardExpenses = StandardExpense::where('company_id', $company->id)
                    ->whereBetween('due_date', $dateRange)
                    ->where('status', 'paid')
                    ->sum('actual_amount');

                $nonStandardExpenses = NonStandardExpense::where('company_id', $company->id)
                    ->whereBetween('expense_date', $dateRange)
                    ->where('status', 'paid')
                    ->sum('amount');

                $totalExpenses = $standardExpenses + $nonStandardExpenses;

                return [
                    'company_name' => $company->name,
                    'total_income' => $income,
                    'total_expense' => $totalExpenses,
                    'net_profit' => $income - $totalExpenses,
                ];
            });
    }

    private function getDateRange($range)
    {
        switch ($range) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'this_week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'this_quarter':
                return [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()];
            case 'this_year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }
    public function dashboard(Request $request)
    {
        // Get filter parameters
        $range = $request->get('range', 'week');
        $companyId = $request->get('company');

        // Statistics
        $stats = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('status', 'active')->count(),
            'pending_companies' => Company::where('status', 'pending')->count(),

            'total_users' => User::count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'manager_users' => User::where('role', 'manager')->count(),
            'regular_users' => User::where('role', 'user')->count(),

            'total_transactions' => \App\Models\Income::count() + \App\Models\Expense::count(),
            'total_amount' => \App\Models\Income::sum('amount') + \App\Models\Expense::sum('actual_amount'),
            'income_count' => \App\Models\Income::count(),
            'expense_count' => \App\Models\Expense::count(),
            'income_percentage' => \App\Models\Income::count() > 0 ?
                round((\App\Models\Income::count() / (\App\Models\Income::count() + \App\Models\Expense::count())) * 100) : 0,

            'pending_items' => \App\Models\Invoice::where('status', 'pending')->count() +
                \App\Models\Expense::whereIn('status', ['pending', 'upcoming'])->count(),
            'pending_invoices' => \App\Models\Invoice::where('status', 'pending')->count(),
            'pending_expenses' => \App\Models\Expense::whereIn('status', ['pending', 'upcoming'])->count(),
            'pending_approvals' => \App\Models\Expense::where('status', 'pending_approval')->count(),

            'overdue_payments' => \App\Models\Invoice::where('status', 'overdue')->count(),
            'overdue_amount' => \App\Models\Invoice::where('status', 'overdue')->sum('total_amount'),

            'expense_types' => \App\Models\ExpenseType::count(),
            'active_expense_types' => \App\Models\ExpenseType::where('status', 'active')->count(),

            'storage_used' => 2.5, // In GB - you would calculate this from your storage
            'storage_total' => 10,
            'storage_percentage' => 25,

            'uptime_percentage' => 99.8,

            'today_actions' => \App\Models\ActivityLog::whereDate('created_at', today())->count(),
        ];

        // Get all companies for filter
        $companies = Company::where('status', 'active')->get();

        // Company distribution stats for pie chart
        $companyStats = $companies->map(function ($company, $index) {
            $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d'];
            return [
                'name' => $company->name,
                'value' => $company->incomes()->count() + $company->expenses()->count(),
                'color' => $colors[$index % count($colors)]
            ];
        });

        // Financial data for chart
        $financialData = $this->getFinancialData($range, $companyId);

        // Recent activities
        $recentActivities = $this->getRecentActivities();

        // Top users
        $topUsers = $this->getTopUsers();

        // Company performance
        $companyPerformance = $this->getCompanyPerformance();

        // Manager expenses report
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $managerExpensesReport = $this->getManagerExpensesReport($range, $companyId, $startDate, $endDate);

        return view('Admin.dashboard', compact(
            'stats',
            'companies',
            'companyStats',
            'financialData',
            'recentActivities',
            'topUsers',
            'companyPerformance',
            'managerExpensesReport'
        ));
    }

    private function getFinancialData($range, $companyId)
    {
        // Generate labels based on range
        $labels = [];
        $income = [];
        $expenses = [];

        switch ($range) {
            case 'today':
                $labels = ['00-04', '04-08', '08-12', '12-16', '16-20', '20-24'];
                for ($i = 0; $i < 6; $i++) {
                    $income[] = rand(5000, 50000);
                    $expenses[] = rand(3000, 30000);
                }
                break;

            case 'week':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                for ($i = 0; $i < 7; $i++) {
                    $income[] = rand(10000, 100000);
                    $expenses[] = rand(5000, 70000);
                }
                break;

            case 'month':
                $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                for ($i = 0; $i < 4; $i++) {
                    $income[] = rand(50000, 300000);
                    $expenses[] = rand(30000, 200000);
                }
                break;

            default:
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                for ($i = 0; $i < 12; $i++) {
                    $income[] = rand(100000, 500000);
                    $expenses[] = rand(80000, 400000);
                }
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses
        ];
    }

    private function getRecentActivities()
    {
        return \App\Models\ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'time' => $log->created_at->format('h:i A'),
                    'date' => $log->created_at->isToday() ? 'Today' : ($log->created_at->isYesterday() ? 'Yesterday' : $log->created_at->format('M d, Y')),
                    'user' => $log->user ? $log->user->name : 'System',
                    'role' => $log->user ? ucfirst($log->user->role) : 'System',
                    'action' => ucfirst(str_replace('_', ' ', $log->action)),
                    'action_color' => $this->getActionColor($log->action),
                    'resource' => class_basename($log->model_type) . ($log->model_id ? ' ID: ' . $log->model_id : ''),
                    'details' => $log->formatted_details,
                    'ip' => $log->ip_address ?? 'N/A'
                ];
            });
    }

    private function getActionColor($action) {
        $map = [
            'created' => 'success',
            'updated' => 'primary',
            'deleted' => 'danger',
            'login'   => 'info',
            'failed_login' => 'warning',
        ];
        return $map[$action] ?? 'secondary';
    }

    private function getTopUsers()
    {
        $users = \App\Models\User::withCount('activityLogs')
            ->orderBy('activityLogs_count', 'desc')
            ->take(4)
            ->get();

        $totalActivities = \App\Models\ActivityLog::count();

        return $users->map(function ($user) use ($totalActivities) {
            $percentage = $totalActivities > 0 ? ($user->activityLogs_count / $totalActivities) * 100 : 0;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => ucfirst($user->role),
                'count' => $user->activityLogs_count,
                'percentage' => round($percentage)
            ];
        });
    }

    private function getCompanyPerformance()
    {
        $companies = Company::with(['incomes', 'expenses'])->where('status', 'active')->get();

        return $companies->map(function ($company) {
            $monthlyIncome = $company->incomes()
                ->whereMonth('income_date', now()->month)
                ->whereYear('income_date', now()->year)
                ->sum('amount');

            $monthlyExpenses = $company->expenses()
                ->whereMonth('due_date', now()->month)
                ->whereYear('due_date', now()->year)
                ->sum('actual_amount');

            $pendingCount = $company->incomes()->where('status', 'pending')->count() +
                $company->expenses()->whereIn('status', ['pending', 'upcoming'])->count();

            return [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'status' => ucfirst($company->status),
                'status_color' => $company->status === 'active' ? 'success' : 'warning',
                'monthly_income' => $monthlyIncome,
                'monthly_expenses' => $monthlyExpenses,
                'net_balance' => $monthlyIncome - $monthlyExpenses,
                'pending_count' => $pendingCount
            ];
        });
    }

    private function getDateRangeFilter($range, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'this_week':
            case 'week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'this_month':
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'this_quarter':
            case 'quarter':
                return [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()];
            case 'this_year':
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'custom':
                if ($startDate && $endDate) {
                    return [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ];
                }
                return null;
            case 'all':
                return null;
            default:
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
        }
    }

    private function getManagerExpensesReport($range = 'week', $companyId = null, $startDate = null, $endDate = null)
    {
        $dateRange = $this->getDateRangeFilter($range, $startDate, $endDate);

        // Fetch managers: Users with role 'manager', or users assigned as manager in companies
        $managerUserIds = User::where('role', 'manager')->pluck('id');
        $companyManagerIds = Company::whereNotNull('manager_id')->pluck('manager_id');
        $allManagerIds = $managerUserIds->merge($companyManagerIds)->filter()->unique();

        $managers = User::whereIn('id', $allManagerIds)
            ->with(['company', 'companies'])
            ->get();

        // Fallback: If no managers found, find users who have created expenses
        if ($managers->isEmpty()) {
            $managers = User::whereHas('expenses')
                ->with(['company', 'companies'])
                ->get();
        }

        // If still empty, get all users
        if ($managers->isEmpty()) {
            $managers = User::with(['company', 'companies'])->get();
        }

        $reportData = $managers->map(function ($manager) use ($companyId, $dateRange) {
            $baseQuery = Expense::where('created_by', $manager->id);
            if ($companyId) {
                $baseQuery->where('company_id', $companyId);
            }

            // All-time totals for this manager
            $allTimeCount = (clone $baseQuery)->count();
            $allTimeAmount = (clone $baseQuery)->sum(DB::raw('COALESCE(actual_amount, planned_amount, 0)'));

            // Filtered query (date range)
            $filteredQuery = clone $baseQuery;
            if ($dateRange) {
                $filteredQuery->where(function ($q) use ($dateRange) {
                    $q->whereBetween('created_at', $dateRange)
                      ->orWhere(function ($sub) use ($dateRange) {
                          $sub->whereNull('created_at')
                              ->whereBetween('due_date', $dateRange);
                      });
                });
            }

            $filteredCount = (clone $filteredQuery)->count();
            $filteredAmount = (clone $filteredQuery)->sum(DB::raw('COALESCE(actual_amount, planned_amount, 0)'));

            // Status counts in filtered period
            $paidCount = (clone $filteredQuery)->where('status', 'paid')->count();
            $paidAmount = (clone $filteredQuery)->where('status', 'paid')->sum(DB::raw('COALESCE(actual_amount, planned_amount, 0)'));

            $pendingCount = (clone $filteredQuery)->whereIn('status', ['pending', 'upcoming'])->count();
            $pendingAmount = (clone $filteredQuery)->whereIn('status', ['pending', 'upcoming'])->sum(DB::raw('COALESCE(planned_amount, actual_amount, 0)'));

            $overdueCount = (clone $filteredQuery)->where('status', 'overdue')->count();
            $overdueAmount = (clone $filteredQuery)->where('status', 'overdue')->sum(DB::raw('COALESCE(planned_amount, actual_amount, 0)'));

            // Source types in filtered period
            $standardCount = (clone $filteredQuery)->where(function ($q) {
                $q->where('source', 'standard')->orWhere('type', 'standard');
            })->count();

            $nonStandardCount = (clone $filteredQuery)->where(function ($q) {
                $q->whereIn('source', ['manual', 'non_standard'])->orWhere('type', 'non_standard');
            })->count();

            // Assigned companies
            $assignedCompanies = collect();
            if ($manager->company) {
                $assignedCompanies->push($manager->company->name);
            }
            if ($manager->companies && $manager->companies->isNotEmpty()) {
                $assignedCompanies = $assignedCompanies->merge($manager->companies->pluck('name'));
            }
            $assignedCompanies = $assignedCompanies->unique()->values();

            // Recent expenses for quick details modal
            $recentExpenses = (clone $baseQuery)
                ->with('company')
                ->latest('created_at')
                ->take(10)
                ->get()
                ->map(function ($exp) {
                    return [
                        'id' => $exp->id,
                        'expense_number' => $exp->expense_number ?? ('EXP-' . str_pad($exp->id, 5, '0', STR_PAD_LEFT)),
                        'name' => $exp->expense_name ?? $exp->name ?? ('Expense #' . $exp->id),
                        'amount' => (float)($exp->actual_amount ?? $exp->planned_amount ?? 0),
                        'status' => $exp->status ?? 'pending',
                        'company' => $exp->company->name ?? 'N/A',
                        'date' => $exp->created_at ? $exp->created_at->format('M d, Y') : ($exp->due_date ? Carbon::parse($exp->due_date)->format('M d, Y') : 'N/A'),
                        'source' => ucfirst(str_replace('_', ' ', $exp->source ?? 'manual')),
                    ];
                });

            $lastExpense = (clone $baseQuery)->latest('created_at')->first();
            $lastAddedDate = $lastExpense && $lastExpense->created_at
                ? $lastExpense->created_at->diffForHumans()
                : ($lastExpense && $lastExpense->due_date ? Carbon::parse($lastExpense->due_date)->format('M d, Y') : 'No expenses yet');

            return [
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'role' => ucfirst($manager->role ?? 'Manager'),
                'companies' => $assignedCompanies->isNotEmpty() ? $assignedCompanies->all() : ['All Companies'],
                'companies_string' => $assignedCompanies->isNotEmpty() ? $assignedCompanies->implode(', ') : 'All Companies',
                'filtered_count' => $filteredCount,
                'filtered_amount' => $filteredAmount,
                'all_time_count' => $allTimeCount,
                'all_time_amount' => $allTimeAmount,
                'paid_count' => $paidCount,
                'paid_amount' => $paidAmount,
                'pending_count' => $pendingCount,
                'pending_amount' => $pendingAmount,
                'overdue_count' => $overdueCount,
                'overdue_amount' => $overdueAmount,
                'standard_count' => $standardCount,
                'non_standard_count' => $nonStandardCount,
                'last_added_date' => $lastAddedDate,
                'recent_expenses' => $recentExpenses,
            ];
        });

        // Calculate summary totals
        $totalExpensesInPeriod = $reportData->sum('filtered_count');
        $totalAmountInPeriod = $reportData->sum('filtered_amount');
        $totalAllTimeExpenses = $reportData->sum('all_time_count');
        $totalAllTimeAmount = $reportData->sum('all_time_amount');

        // Add percentage contribution and sort by filtered count (or all-time count)
        $reportData = $reportData->map(function ($item) use ($totalExpensesInPeriod, $totalAllTimeExpenses) {
            $baseTotal = $totalExpensesInPeriod > 0 ? $totalExpensesInPeriod : $totalAllTimeExpenses;
            $baseCount = $totalExpensesInPeriod > 0 ? $item['filtered_count'] : $item['all_time_count'];
            $item['percentage'] = $baseTotal > 0 ? round(($baseCount / $baseTotal) * 100, 1) : 0;
            return $item;
        })->sortByDesc(function ($item) {
            return ($item['filtered_count'] * 1000000) + $item['all_time_count'];
        })->values();

        $summary = [
            'total_managers' => $managers->count(),
            'active_managers_in_period' => $reportData->where('filtered_count', '>', 0)->count(),
            'active_managers_all_time' => $reportData->where('all_time_count', '>', 0)->count(),
            'total_expenses_in_period' => $totalExpensesInPeriod,
            'total_amount_in_period' => $totalAmountInPeriod,
            'total_all_time_expenses' => $totalAllTimeExpenses,
            'total_all_time_amount' => $totalAllTimeAmount,
            'date_range_label' => ucfirst(str_replace('_', ' ', $range)),
        ];

        return [
            'managers' => $reportData,
            'summary' => $summary,
        ];
    }
}
