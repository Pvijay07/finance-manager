<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckOverduePayments extends Command
{
    protected $signature = 'payments:check-overdue';
    protected $description = 'Check for overdue payments across Invoices, Incomes, and Expenses and update their status';

    public function handle()
    {
        $this->info('Starting overdue payments check...');

        $today = Carbon::today();
        
        $invoicesUpdated = 0;
        $incomesUpdated = 0;
        $expensesUpdated = 0;

        // Check Invoices
        $invoicesUpdated = Invoice::where('due_date', '<', $today)
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'draft'])
            ->update(['status' => 'overdue']);
            
        // Check Incomes
        $incomesUpdated = Income::where('due_date', '<', $today)
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'settle'])
            ->update(['status' => 'overdue']);
            
        // Check Expenses
        $expensesUpdated = Expense::where('due_date', '<', $today)
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'settle'])
            ->update(['status' => 'overdue']);

        $this->info("Completed overdue checks.");
        $this->info("Updated {$invoicesUpdated} Invoices, {$incomesUpdated} Incomes, and {$expensesUpdated} Expenses to overdue.");

        return 0;
    }
}
