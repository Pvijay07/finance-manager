<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expense;
use Carbon\Carbon;

class GenerateStandardExpenses extends Command
{
    protected $signature = 'expenses:generate-standard';
    protected $description = 'Generate standard monthly expenses';

    public function handle()
    {
        $this->info('Generating standard expenses...');

        $today = Carbon::today();
        $generatedCount = 0;
        $errorCount = 0;

        // Get all active standard expense templates (parent_id is null)
        $templates = Expense::where('source', 'standard')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['taxes'])
            ->get();

        foreach ($templates as $template) {
            try {
                if ($this->shouldGenerateExpense($template, $today)) {
                    $this->generateExpenseFromTemplate($template, $today);
                    $generatedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Error generating expense for template {$template->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Completed! Generated {$generatedCount} standard expenses. Errors: {$errorCount}");

        // Log the generation
        \Illuminate\Support\Facades\Log::info("Standard expenses auto-generated for the day: {$generatedCount} generated.");

        return 0;
    }

    private function shouldGenerateExpense($template, Carbon $today)
    {
        if (empty($template->frequency) || empty($template->due_day)) {
            return false;
        }

        $targetDueDate = $this->calculateTargetDueDate($template, $today);

        if (!$targetDueDate) {
            return false;
        }

        // Check if an expense for this target month & year already exists (template itself or child)
        $alreadyExists = Expense::where(function ($q) use ($template) {
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

    private function calculateTargetDueDate($template, Carbon $today)
    {
        $dueDay = (int) ($template->due_day ?: 1);

        // Find the latest due date among the template and its children
        $latestExpense = Expense::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereNotNull('due_date')
        ->orderBy('due_date', 'desc')
        ->first();

        if ($latestExpense && $latestExpense->due_date) {
            $lastDueDate = Carbon::parse($latestExpense->due_date);
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

    private function generateExpenseFromTemplate($template, Carbon $today)
    {
        $dueDate = $this->calculateTargetDueDate($template, $today);

        if (!$dueDate) {
            return;
        }

        // Double check if already exists for this period
        $existing = Expense::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereYear('due_date', $dueDate->year)
        ->whereMonth('due_date', $dueDate->month)
        ->first();

        if ($existing) {
            $this->warn("Expense already exists for template {$template->id} for {$dueDate->format('F Y')}");
            return;
        }

        $expense = Expense::create([
            'expense_number'    => Expense::generateNewExpenseNumber(),
            'company_id'        => $template->company_id,
            'category_id'       => $template->category_id,
            'parent_id'         => $template->id,
            'source'            => 'standard',
            'expense_name'      => $template->expense_name,
            'party_name'        => $template->party_name,
            'planned_amount'    => $template->planned_amount,
            'actual_amount'     => $template->actual_amount ?? $template->planned_amount,
            'original_amount'   => $template->original_amount ?? $template->planned_amount,
            'schedule_amount'   => $template->schedule_amount ?? $template->planned_amount,
            'balance_amount'    => $template->balance_amount ?? $template->planned_amount,
            'due_date'          => $dueDate->format('Y-m-d'),
            'due_day'           => $template->due_day,
            'status'            => ($dueDate->gte(Carbon::today())) ? 'upcoming' : 'pending',
            'notes'             => $template->notes ?: "Auto-generated standard expense",
            'is_recurring'      => 0,
            'is_active'         => 1,
            'month_year'        => $dueDate->format('Y-m'),
            'assigned_managers' => $template->assigned_managers,
            'created_by'        => $template->created_by,
        ]);

        if ($template->taxes && $template->taxes->isNotEmpty()) {
            foreach ($template->taxes as $tax) {
                $expense->taxes()->create([
                    'tax_type'       => $tax->tax_type,
                    'tax_percentage' => $tax->tax_percentage,
                    'tax_amount'     => $tax->tax_amount,
                    'payment_status' => 'not_received',
                    'direction'      => 'expense',
                    'taxable_amount' => $tax->taxable_amount ?: ($template->actual_amount ?: $template->planned_amount),
                ]);
            }
        }

        $this->info("Generated expense ID: {$expense->id} due on: {$dueDate->format('Y-m-d')}");
    }
}

