<?php

namespace App\Console\Commands;

use App\Models\Income;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringIncome extends Command
{
    protected $signature = 'income:generate-recurring';
    protected $description = 'Generate recurring income from standard templates';

    public function handle()
    {
        $this->info('Starting recurring income generation...');

        $today = Carbon::today();
        $generatedCount = 0;
        $errorCount = 0;

        // Get all standard income templates
        $templates = Income::where('source', 'standard')
            ->with(['company', 'category', 'taxes', 'lineItems'])
            ->get();

        foreach ($templates as $template) {
            try {
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

    private function shouldGenerateIncome(Income $template, Carbon $today)
    {
        if (empty($template->frequency) || empty($template->due_day)) {
            return false;
        }

        $dueDate = $this->calculateDueDate($template, $today);

        // Standard income might not have reminder_days in the DB, so default to 0
        $reminderDays = $template->reminder_days ?? 0;

        if ($dueDate->isFuture() || $dueDate->isToday()) {
            $reminderDate = $dueDate->copy()->subDays($reminderDays);

            return $today->isSameDay($reminderDate);
        }

        return false;
    }

    private function calculateDueDate(Income $template, Carbon $today)
    {
        $currentYear = $today->year;
        $currentMonth = $today->month;

        switch ($template->frequency) {
            case 'monthly':
                return Carbon::create($currentYear, $currentMonth, $template->due_day);
            case 'quarterly':
                $quarter = ceil($currentMonth / 3);
                $quarterMonth = ($quarter * 3);
                return Carbon::create($currentYear, $quarterMonth, $template->due_day);
            case 'yearly':
                $dueMonth = $template->due_month ?? 1;
                return Carbon::create($currentYear, $dueMonth, $template->due_day);
            default:
                throw new \Exception("Unknown frequency: {$template->frequency}");
        }
    }

    private function generateIncomeFromTemplate(Income $template, Carbon $today)
    {
        $dueDate = $this->calculateDueDate($template, $today);

        // Check if income already exists for this period
        $existingIncome = Income::where('parent_id', $template->id)
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

        $plannedAmount = $template->planned_amount;

        // Create income entry
        $income = Income::create([
            'company_id' => $template->company_id,
            'income_type' => $template->income_type,
            'description' => $template->description ?? "Auto-generated standard income",
            'amount' => $template->amount,
            'actual_amount' => $template->actual_amount,
            'party_name' => $template->party_name,
            'planned_amount' => $template->planned_amount,
            'received_amount' => 0,
            'balance_amount' => $template->planned_amount,
            'tax_type' => $template->tax_type,
            'due_date' => $dueDate,
            'paid_date' => null,
            'status' => 'pending',
            'notes' => $template->notes,
            'client_details' => $template->client_details,
            'parent_id' => $template->id,
            'source' => 'auto_generated',
            'currency' => $template->currency ?? 'INR',
            'month_year' => $dueDate->format('Y-m'),
            'original_amount' => $template->original_amount ?? $template->amount,
        ]);

        if ($template->taxes) {
            foreach ($template->taxes as $tax) {
                $income->taxes()->create([
                    'tax_type' => $tax->tax_type,
                    'tax_percentage' => $tax->tax_percentage,
                    'tax_amount' => $tax->tax_amount,
                ]);
            }
        }

        // Copy line items from template if they exist
        if ($template->lineItems && $template->lineItems()->exists()) {
            foreach ($template->lineItems as $lineItem) {
                $income->lineItems()->create([
                    'description' => $lineItem->description,
                    'quantity' => $lineItem->quantity,
                    'rate' => $lineItem->rate,
                    'amount' => $lineItem->amount,
                ]);
            }
        }

        $this->info("Generated income ID: {$income->id} for template ID: {$template->id} due on: {$dueDate->format('Y-m-d')}");
    }
}
