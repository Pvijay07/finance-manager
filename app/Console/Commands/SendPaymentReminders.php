<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Income;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send or log payment reminders for upcoming invoices and incomes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting payment reminders check...');

        $today = Carbon::today();
        $reminderDays = 3; // e.g. remind 3 days before due date
        $targetDate = $today->copy()->addDays($reminderDays);

        // Find upcoming invoices
        $upcomingInvoices = Invoice::where('due_date', $targetDate)
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'draft'])
            ->get();

        foreach ($upcomingInvoices as $invoice) {
            $msg = "Reminder: Invoice {$invoice->invoice_number} is due on {$invoice->due_date->format('Y-m-d')}.";
            $this->info($msg);
            Log::info('PaymentReminder', ['message' => $msg, 'invoice_id' => $invoice->id]);
            // TODO: Implement actual email/SMS sending logic here if needed
        }

        // Find upcoming incomes
        $upcomingIncomes = Income::where('due_date', $targetDate)
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'settle'])
            ->get();

        foreach ($upcomingIncomes as $income) {
            $msg = "Reminder: Income ID {$income->id} is due on {$income->due_date->format('Y-m-d')}.";
            $this->info($msg);
            Log::info('PaymentReminder', ['message' => $msg, 'income_id' => $income->id]);
            // TODO: Implement actual email/SMS sending logic here if needed
        }

        $this->info("Completed payment reminders check.");
        $this->info("Processed {$upcomingInvoices->count()} invoices and {$upcomingIncomes->count()} incomes.");

        return 0;
    }
}
