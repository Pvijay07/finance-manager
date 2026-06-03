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
        $viewType = $request->get('view', 'summary');
        
        // Get date range
        $dateFilters = $this->getDateRange($dateRange);
        $startDate = $dateFilters['start'] ?? Carbon::now()->startOfMonth();
        $endDate = $dateFilters['end'] ?? Carbon::now()->endOfMonth();
        
        // Get company IDs for the user
        $companyIds = $this->getCompanyIds($user, $companyId);
        
        // Calculate current period stats
        $currentStats = $this->calculateCurrentStats($companyIds, $startDate, $endDate, $dateRange);
        
        // Calculate previous period stats (for comparison)
        $previousStartDate = Carbon::parse($startDate)->subMonth();
        $previousEndDate = Carbon::parse($endDate)->subMonth();
        $previousStats = $this->calculatePreviousStats($companyIds, $previousStartDate, $previousEndDate);
        
        // Get immediate payments
        $immediatePayments = $this->getImmediatePaymentsData($companyIds);
        
        // Get upcoming stats
        $upcomingStats = $this->getUpcomingStatsData($companyIds);
        
        // Get company profit/loss
        $companyProfitLoss = $this->getCompanyProfitLossData($companyIds, $startDate, $endDate);
        
        // Get notifications
        $notifications = $this->getNotificationsData($companyIds);
        
        // Get companies for dropdown
        $companies = $this->getCompaniesForDropdown($user);
        
        return view('Manager.dashboard', compact(
            'currentStats',
            'previousStats',
            'immediatePayments',
            'upcomingStats',
            'companyProfitLoss',
            'notifications',
            'companies',
            'dateRange',
            'companyId',
            'viewType'
        ));
    }

    // API endpoints (keep existing for AJAX calls if needed)
    public function getDashboardData(Request $request)
    {
        $user = auth()->user();
        $companyIds = $this->getCompanyIds($user, $request->company_id);
        $dateRange = $this->getDateRange($request->date_range ?? 'this_month');

        $totalIncome = Income::whereIn('company_id', $companyIds)
            ->whereBetween('income_date', $dateRange)
            ->sum('amount');

        $totalExpenses = $this->calculateTotalExpenses($companyIds, $dateRange);

        return response()->json([
            'metrics' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $totalIncome - $totalExpenses,
                'upcoming_payments' => UpcomingPayment::whereIn('company_id', $companyIds)
                    ->whereIn('status', ['upcoming', 'pending'])
                    ->whereBetween('due_date', [now(), now()->addDays(7)])
                    ->sum('amount'),
            ],
            'immediate_payments' => $this->getImmediatePaymentsForApi($companyIds),
            'company_wise_pl' => $this->getCompanyWisePLForApi($companyIds, $dateRange),
        ]);
    }

    // Helper methods for view data
    private function getCompanyIds($user, $specificCompanyId = null)
    {
        if ($specificCompanyId) {
            // Verify the user has access to this specific company
            $hasAccess = Company::where('id', $specificCompanyId)
                ->where('manager_id', $user->id)
                ->exists();
            
            return $hasAccess ? [$specificCompanyId] : [];
        }

        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->pluck('id')->toArray();
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();
    }

    private function calculateCurrentStats($companyIds, $startDate, $endDate, $dateRange)
    {
        if (empty($companyIds)) {
            return [
                'totalIncome' => 0,
                'totalExpenses' => 0,
                'netProfit' => 0,
                'upcomingPayments' => 0,
                'periodLabel' => $this->getPeriodLabel($dateRange)
            ];
        }

        $totalIncome = Income::whereIn('company_id', $companyIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'received')
            ->sum('amount');

        $totalExpenses = Expense::whereIn('company_id', $companyIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('planned_amount');

        $upcomingPayments = UpcomingPayment::whereIn('company_id', $companyIds)
            ->whereIn('status', ['upcoming', 'pending'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->sum('amount');

        return [
            'totalIncome' => $totalIncome ?? 0,
            'totalExpenses' => $totalExpenses ?? 0,
            'netProfit' => ($totalIncome ?? 0) - ($totalExpenses ?? 0),
            'upcomingPayments' => $upcomingPayments ?? 0,
            'periodLabel' => $this->getPeriodLabel($dateRange)
        ];
    }

    private function calculatePreviousStats($companyIds, $startDate, $endDate)
    {
        if (empty($companyIds)) {
            return [
                'totalIncome' => 0,
                'totalExpenses' => 0,
                'netProfit' => 0
            ];
        }

        $totalIncome = Income::whereIn('company_id', $companyIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'received')
            ->sum('amount');

        $totalExpenses = Expense::whereIn('company_id', $companyIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('planned_amount');

        return [
            'totalIncome' => $totalIncome ?? 0,
            'totalExpenses' => $totalExpenses ?? 0,
            'netProfit' => ($totalIncome ?? 0) - ($totalExpenses ?? 0)
        ];
    }

    private function getImmediatePaymentsData($companyIds)
    {
        if (empty($companyIds)) {
            return collect([]);
        }

        $startDate = Carbon::now()->subDays(1);
        $endDate = Carbon::now()->addDays(3);

        $payments = UpcomingPayment::whereIn('company_id', $companyIds)
            ->whereIn('status', ['upcoming', 'pending', 'overdue'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with('company')
            ->orderBy('due_date')
            ->get();

        // Format data for view
        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'date' => $payment->due_date ? Carbon::parse($payment->due_date)->format('d M Y') : 'N/A',
                'company' => $payment->company->name ?? 'N/A',
                'name' => $payment->description ?? 'Payment',
                'type' => ucfirst($payment->type ?? 'payment'),
                'party' => $payment->party_name ?? 'N/A',
                'amount' => $payment->amount ?? 0,
                'status' => $payment->status ?? 'pending'
            ];
        });
    }

    private function getUpcomingStatsData($companyIds)
    {
        if (empty($companyIds)) {
            return [
                'debits' => ['amount' => 0, 'count' => 0],
                'credits' => ['amount' => 0, 'count' => 0]
            ];
        }

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $debits = UpcomingPayment::whereIn('company_id', $companyIds)
            ->where('type', 'debit')
            ->whereIn('status', ['upcoming', 'pending'])
            ->whereBetween('due_date', [$startDate, $endDate]);

        $credits = UpcomingPayment::whereIn('company_id', $companyIds)
            ->where('type', 'credit')
            ->whereIn('status', ['upcoming', 'pending'])
            ->whereBetween('due_date', [$startDate, $endDate]);

        return [
            'debits' => [
                'amount' => $debits->sum('amount') ?? 0,
                'count' => $debits->count()
            ],
            'credits' => [
                'amount' => $credits->sum('amount') ?? 0,
                'count' => $credits->count()
            ]
        ];
    }

    private function getCompanyProfitLossData($companyIds, $startDate, $endDate)
    {
        if (empty($companyIds)) {
            return [];
        }

        return Company::whereIn('id', $companyIds)
            ->get()
            ->map(function ($company) use ($startDate, $endDate) {
                $income = Income::where('company_id', $company->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'received')
                    ->sum('amount');
                
                $expenses = Expense::where('company_id', $company->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'paid')
                    ->sum('planned_amount');

                return [
                    'name' => $company->name,
                    'income' => $income ?? 0,
                    'expenses' => $expenses ?? 0,
                    'profit' => ($income ?? 0) - ($expenses ?? 0)
                ];
            });
    }

    private function getNotificationsData($companyIds)
    {
        if (empty($companyIds)) {
            return [];
        }

        $notifications = [];

        // Overdue payments
        $overdueCount = UpcomingPayment::whereIn('company_id', $companyIds)
            ->where('due_date', '<', Carbon::now())
            ->whereIn('status', ['pending', 'upcoming'])
            ->count();

        if ($overdueCount > 0) {
            $notifications[] = [
                'type' => 'danger',
                'icon' => 'exclamation-triangle',
                'message' => "You have $overdueCount overdue payments",
                'link' => '#'
            ];
        }

        // Upcoming payments
        $upcomingCount = UpcomingPayment::whereIn('company_id', $companyIds)
            ->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(7)])
            ->whereIn('status', ['pending', 'upcoming'])
            ->count();

        if ($upcomingCount > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'calendar-alt',
                'message' => "You have $upcomingCount payments due in next 7 days",
                'link' => '#'
            ];
        }

        // Pending expenses
        $pendingExpenseCount = Expense::whereIn('company_id', $companyIds)
            ->where('status', 'pending')
            ->count();

        if ($pendingExpenseCount > 0) {
            $notifications[] = [
                'type' => 'info',
                'icon' => 'file-invoice-dollar',
                'message' => "You have $pendingExpenseCount pending expenses",
                'link' => route('manager.expenses', ['status' => 'pending'])
            ];
        }

        // Add default notification if none
        if (empty($notifications)) {
            $notifications[] = [
                'type' => 'success',
                'icon' => 'check-circle',
                'message' => "All payments are up to date",
                'link' => '#'
            ];
        }

        return $notifications;
    }

    private function getCompaniesForDropdown($user)
    {
        if ($user->isAdmin() || $user->isCA()) {
            return Company::where('status', 'active')->get(['id', 'name']);
        }

        return Company::where('manager_id', $user->id)
            ->where('status', 'active')
            ->get(['id', 'name']);
    }

    private function getDateRange($range)
    {
        switch ($range) {
            case 'today':
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay()
                ];
            case 'week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            case 'quarter':
                return [
                    'start' => Carbon::now()->startOfQuarter(),
                    'end' => Carbon::now()->endOfQuarter()
                ];
            case 'year':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
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
            'year' => 'This Year'
        ];

        return $labels[$range] ?? 'This Month';
    }

    // API helper methods (keep existing)
    private function calculateTotalExpenses($companyIds, $dateRange)
    {
        if (empty($companyIds)) return 0;

        $standardExpenses = Expense::whereIn('company_id', $companyIds)
            ->where('source', 'standard')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'paid')
            ->sum('planned_amount');

        $nonStandardExpenses = Expense::whereIn('company_id', $companyIds)
            ->where('source', 'manual')
            ->whereBetween('created_at', $dateRange)
            ->where('status', 'paid')
            ->sum('planned_amount');

        return ($standardExpenses ?? 0) + ($nonStandardExpenses ?? 0);
    }

    private function getImmediatePaymentsForApi($companyIds)
    {
        if (empty($companyIds)) return collect([]);

        return UpcomingPayment::whereIn('company_id', $companyIds)
            ->whereIn('status', ['upcoming', 'pending', 'overdue'])
            ->whereBetween('due_date', [now()->subDays(1), now()->addDays(3)])
            ->with('company')
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    private function getCompanyWisePLForApi($companyIds, $dateRange)
    {
        if (empty($companyIds)) return [];

        return Company::whereIn('id', $companyIds)
            ->get()
            ->map(function ($company) use ($dateRange) {
                $income = Income::where('company_id', $company->id)
                    ->whereBetween('created_at', $dateRange)
                    ->where('status', 'received')
                    ->sum('amount');

                $expenses = $this->calculateTotalExpenses([$company->id], $dateRange);

                return [
                    'company_name' => $company->name,
                    'total_income' => $income ?? 0,
                    'total_expense' => $expenses ?? 0,
                    'net_profit' => ($income ?? 0) - ($expenses ?? 0),
                ];
            });
    }
}