<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expense;
use App\Models\ExpenseGenerationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateStandardExpenses extends Command
{
    protected $signature = 'expenses:generate-standard';
    protected $description = 'Generate standard recurring expenses based on frequency and due date, catching up any passed/missed periods';

    public function handle()
    {
        $this->info('Generating standard expenses (including catching up past due periods)...');

        $today = Carbon::today();
        $generatedCount = 0;
        $errorCount = 0;
        $details = [];

        // Get all active standard expense templates (parent_id is null)
        $templates = Expense::where('source', 'standard')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->whereNotNull('frequency')
            ->whereNotNull('due_day')
            ->with(['taxes'])
            ->get();

        $this->info("Found {$templates->count()} active templates to check.");

        foreach ($templates as $template) {
            try {
                // Loop to catch up any missed cycles until target date is in the future beyond reminder window
                while (true) {
                    $nextDueDate = $this->calculateNextDueDate($template);

                    if (!$nextDueDate) {
                        break;
                    }

                    // Check reminder window
                    $reminderDays = (int) ($template->reminder_days ?? 0);
                    $triggerDate = $nextDueDate->copy()->subDays($reminderDays)->startOfDay();

                    // If today has not reached the trigger date for this next period, stop for this template
                    if ($today->startOfDay()->lt($triggerDate)) {
                        break;
                    }

                    // Generate the expense for this period
                    $expense = $this->generateExpenseForDate($template, $nextDueDate);
                    if ($expense) {
                        $generatedCount++;
                        $details[] = [
                            'template_id' => $template->id,
                            'expense_id' => $expense->id,
                            'expense_name' => $template->expense_name,
                            'company_id' => $template->company_id,
                            'due_date' => $expense->due_date->format('Y-m-d'),
                            'status' => $expense->status,
                        ];
                    } else {
                        // Prevent infinite loop if generation was skipped/already exists
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error generating expense for template {$template->id}: " . $e->getMessage());
                Log::error("Standard expense generation error for template {$template->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        // Log the generation run
        try {
            ExpenseGenerationLog::create([
                'run_date' => $today,
                'status' => $errorCount > 0 ? 'partial' : 'success',
                'total_generated' => $generatedCount,
                'details' => $details,
                'triggered_by' => auth()->check() ? auth()->user()->name : 'system',
                'trigger_type' => auth()->check() ? 'manual' : 'scheduled',
            ]);
        } catch (\Exception $e) {
            Log::warning("Could not write ExpenseGenerationLog: " . $e->getMessage());
        }

        $this->info("Completed! Generated {$generatedCount} standard expenses. Errors: {$errorCount}");
        Log::info("Standard expenses generation: {$generatedCount} generated, {$errorCount} errors.");

        return 0;
    }

    /**
     * Calculate the earliest missing due date for a template based on its frequency.
     */
    private function calculateNextDueDate($template)
    {
        $dueDay = (int) ($template->due_day ?: 1);

        // Find the most recent expense for this template (including template itself and children)
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

        // Fallback to template's initial due_date or current month
        if ($template->due_date) {
            return Carbon::parse($template->due_date)->startOfDay();
        }

        return Carbon::today()->day(min($dueDay, Carbon::today()->daysInMonth))->startOfDay();
    }

    /**
     * Create child expense for the specified due date
     */
    private function generateExpenseForDate($template, Carbon $dueDate)
    {
        // Check if an expense for this target month & year already exists
        $alreadyExists = Expense::where(function ($q) use ($template) {
            $q->where('id', $template->id)
              ->orWhere('parent_id', $template->id);
        })
        ->whereYear('due_date', $dueDate->year)
        ->whereMonth('due_date', $dueDate->month)
        ->exists();

        if ($alreadyExists) {
            return null;
        }

        $today = Carbon::today();
        // Determine status: if due date is in the past, status is 'overdue' or 'pending'; otherwise 'upcoming'
        $status = 'upcoming';
        if ($dueDate->lt($today)) {
            $status = 'overdue';
        } elseif ($dueDate->isToday()) {
            $status = 'pending';
        }

        $expense = Expense::create([
            'expense_number'    => Expense::generateNewExpenseNumber(),
            'company_id'        => $template->company_id,
            'category_id'       => $template->category_id,
            'parent_id'         => $template->id,
            'source'            => 'standard',
            'expense_name'      => $template->expense_name,
            'party_name'        => $template->party_name,
            'mobile_number'     => $template->mobile_number,
            'planned_amount'    => $template->planned_amount,
            'actual_amount'     => $template->actual_amount ?? $template->planned_amount,
            'original_amount'   => $template->original_amount ?? $template->planned_amount,
            'schedule_amount'   => $template->schedule_amount ?? $template->planned_amount,
            'balance_amount'    => $template->balance_amount ?? $template->planned_amount,
            'due_date'          => $dueDate->format('Y-m-d'),
            'due_day'           => $template->due_day,
            'frequency'         => $template->frequency,
            'amount_mode'       => $template->amount_mode,
            'status'            => $status,
            'notes'             => $template->notes ?: "Auto-generated standard expense",
            'is_recurring'      => 0,
            'is_active'         => 1,
            'month_year'        => $dueDate->format('Y-m'),
            'assigned_managers' => $template->assigned_managers,
            'created_by'        => $template->created_by,
            'tax_type'          => $template->tax_type,
            'reminder_days'     => $template->reminder_days,
        ]);

        // Copy taxes from template
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

        $this->info("Generated expense ID: {$expense->id} for '{$template->expense_name}' due on: {$dueDate->format('Y-m-d')} [Status: {$status}]");

        return $expense;
    }
}
