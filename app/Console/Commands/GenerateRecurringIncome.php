<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Income;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringIncome extends Command
{
    protected $signature = 'income:generate-recurring';
    protected $description = 'Generate recurring income from invoice templates';

    public function handle()
    {
        $this->info('Starting recurring income generation...');

        $today = Carbon::today();
        $generatedCount = 0;
        $errorCount = 0;

        // Get all active invoice templates
        $templates = Invoice::where('is_active', true)
            ->where('source', 'standard')
            ->with(['company', 'category', 'taxes'])
            ->get();

        foreach ($templates as $template) {
            try {
                // Check if income should be generated for this template
                if ($this->shouldGenerateIncome($template, $today)) {
                    $this->generateIncomeFromTemplate($template, $today);
                    $generatedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Error generating income for template {$template->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Completed! Generated {$generatedCount} income entries. Errors: {$errorCount}");

        return 0;
    }

    private function shouldGenerateIncome(Invoice $template, Carbon $today)
    {
        // Check if template has a frequency set
        if (empty($template->frequency) || empty($template->due_day)) {
            return false;
        }

        // Calculate the due date for current cycle
        $dueDate = $this->calculateDueDate($template, $today);

        // Check if due date is in the future and within reminder days
        if ($dueDate->isFuture()) {
            $reminderDate = $dueDate->copy()->subDays($template->reminder_days);

            // Generate income when we reach the reminder date (before due date)
            return $today->isSameDay($reminderDate);
        }

        return false;
    }

    private function calculateDueDate(Invoice $template, Carbon $today)
    {
        $currentYear = $today->year;
        $currentMonth = $today->month;

        // Calculate due date based on frequency
        switch ($template->frequency) {
            case 'monthly':
                // Due on X day of current month
                $dueDate = Carbon::create($currentYear, $currentMonth, $template->due_day);
                break;

            case 'quarterly':
                // Determine current quarter
                $quarter = ceil($currentMonth / 3);
                $quarterMonth = ($quarter * 3); // Last month of quarter
                $dueDate = Carbon::create($currentYear, $quarterMonth, $template->due_day);
                break;

            case 'yearly':
                // Due on X day of specific month (if template has due_month field)
                $dueMonth = $template->due_month ?? 1;
                $dueDate = Carbon::create($currentYear, $dueMonth, $template->due_day);
                break;

            default:
                throw new \Exception("Unknown frequency: {$template->frequency}");
        }

        return $dueDate;
    }

    private function generateIncomeFromTemplate(Invoice $template, Carbon $today)
    {
        // Calculate due date
        $dueDate = $this->calculateDueDate($template, $today);

        // Check if income already exists for this period
        $existingIncome = Income::where('invoice_template_id', $template->id)
            ->whereYear('due_date', $dueDate->year)
            ->whereMonth('due_date', $dueDate->month)
            ->first();

        if ($existingIncome) {
            $this->warn("Income already exists for template {$template->id} for {$dueDate->format('F Y')}");
            return;
        }

        // Calculate total amount with taxes
        $gstTotal = 0;
        $tdsTotal = 0;

        if ($template->taxes) {
            foreach ($template->taxes as $tax) {
                if ($tax->tax_type === 'gst') {
                    $gstTotal = $tax->tax_amount;
                } elseif ($tax->tax_type === 'tds') {
                    $tdsTotal = $tax->tax_amount;
                }
            }
        }

        $plannedAmount = $template->planned_amount + $gstTotal - $tdsTotal;

        // Create income entry
        $income = Income::create([
            'invoice_template_id' => $template->id,
            'company_id' => $template->company_id,
            'category_id' => $template->category_id,
            'client_name' => $template->client_name ?: ($template->company?->name ?? 'Unknown Client'),
            'planned_amount' => $plannedAmount,
            'paid_amount' => 0,
            'balance_amount' => $plannedAmount,
            'gst_amount' => $gstTotal,
            'tds_amount' => $tdsTotal,
            'gst_percentage' => $template->gst_percentage ?? 0,
            'tds_percentage' => $template->tds_percentage ?? 0,
            'tax_type' => $template->tax_type,
            'due_date' => $dueDate,
            'paid_date' => null,
            'status' => 'pending',
            'payment_mode' => '',
            'party_name' => $template->party_name,
            'mobile_number' => $template->mobile_number,
            'notes' => "Auto-generated from invoice template: {$template->expense_name}",
            'source' => 'auto_generated',
        ]);

        // Copy line items from template if they exist
        if (method_exists($template, 'lineItems') && $template->lineItems()->exists()) {
            foreach ($template->lineItems as $lineItem) {
                $income->lineItems()->create([
                    'description' => $lineItem->description,
                    'quantity' => $lineItem->quantity,
                    'rate' => $lineItem->rate,
                    'amount' => $lineItem->amount,
                ]);
            }
        }

        $this->info("Generated income ID: {$income->id} for template: {$template->expense_name} due on: {$dueDate->format('Y-m-d')}");
    }
}
