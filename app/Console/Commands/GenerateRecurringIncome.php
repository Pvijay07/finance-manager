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

        // Get all active standard income templates (templates only, where parent_id is null)
        $templates = Income::where('source', 'standard')
            ->whereNull('parent_id')
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

        $targetDueDate = $this->calculateTargetDueDate($template, $today);

        if (!$targetDueDate) {
            return false;
        }

        // Check if income already exists for this target period (template itself or child)
        $alreadyExists = Income::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereYear('due_date', $targetDueDate->year)
        ->whereMonth('due_date', $targetDueDate->month)
        ->exists();

        if ($alreadyExists) {
            return false;
        }

        $reminderDays = (int) ($template->reminder_days ?? 0);
        $triggerDate = $targetDueDate->copy()->subDays($reminderDays)->startOfDay();

        return $today->startOfDay()->gte($triggerDate);
    }

    private function calculateTargetDueDate(Income $template, Carbon $today)
    {
        $dueDay = (int) ($template->due_day ?: 1);

        // Find the latest due date among template and any of its children
        $latestIncome = Income::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereNotNull('due_date')
        ->orderBy('due_date', 'desc')
        ->first();

        if ($latestIncome && $latestIncome->due_date) {
            $lastDueDate = Carbon::parse($latestIncome->due_date);
            $nextDate = $lastDueDate->copy();

            switch ($template->frequency) {
                case 'monthly':
                    $nextDate->addMonthNoOverflow();
                    break;
                case 'quarterly':
                    $nextDate->addMonthsNoOverflow(3);
                    break;
                case 'yearly':
                    $nextDate->addYearNoOverflow();
                    break;
                default:
                    $nextDate->addMonthNoOverflow();
                    break;
            }

            $nextDate->day(min($dueDay, $nextDate->daysInMonth));
            return $nextDate->startOfDay();
        }

        // Fallback if no prior due date exists
        $currentMonthTarget = $today->copy()->day(min($dueDay, $today->daysInMonth))->startOfDay();
        return $currentMonthTarget;
    }

    private function generateIncomeFromTemplate(Income $template, Carbon $today)
    {
        $dueDate = $this->calculateTargetDueDate($template, $today);

        if (!$dueDate) {
            return;
        }

        // Check if income already exists for this period
        $existingIncome = Income::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereYear('due_date', $dueDate->year)
        ->whereMonth('due_date', $dueDate->month)
        ->first();

        if ($existingIncome) {
            $this->warn("Income already exists for template {$template->id} for {$dueDate->format('F Y')}");
            return;
        }

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
            'due_date' => $dueDate->format('Y-m-d'),
            'paid_date' => null,
            'status' => ($dueDate->gte(Carbon::today())) ? 'upcoming' : 'pending',
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

