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
    protected $description = 'Generate standard recurring expenses based on frequency and due date';

    public function handle()
    {
        $this->info('Generating standard expenses...');

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
                if ($this->shouldGenerateExpense($template, $today)) {
                    $expense = $this->generateExpenseFromTemplate($template, $today);
                    if ($expense) {
                        $generatedCount++;
                        $details[] = [
                            'template_id' => $template->id,
                            'expense_id' => $expense->id,
                            'expense_name' => $template->expense_name,
                            'company_id' => $template->company_id,
                            'due_date' => $expense->due_date->format('Y-m-d'),
                        ];
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
                'triggered_by' => 'system',
                'trigger_type' => 'scheduled',
            ]);
        } catch (\Exception $e) {
            Log::warning("Could not write ExpenseGenerationLog: " . $e->getMessage());
        }

        $this->info("Completed! Generated {$generatedCount} standard expenses. Errors: {$errorCount}");
        Log::info("Standard expenses generation: {$generatedCount} generated, {$errorCount} errors.");

        return 0;
    }

    private function shouldGenerateExpense($template, Carbon $today)
    {
        $targetDueDate = $this->calculateTargetDueDate($template, $today);

        if (!$targetDueDate) {
            return false;
        }

        // Check if an expense for this target month & year already exists
        // Only check CHILDREN (generated expenses), NOT the template itself
        $alreadyExists = Expense::where('parent_id', $template->id)
            ->whereYear('due_date', $targetDueDate->year)
            ->whereMonth('due_date', $targetDueDate->month)
            ->exists();

        if ($alreadyExists) {
            return false;
        }

        // Also check if the template itself covers this month (first month after creation)
        // The template serves as the expense for its own due_date month
        $templateDueDate = Carbon::parse($template->due_date);
        if ($templateDueDate->year == $targetDueDate->year && $templateDueDate->month == $targetDueDate->month) {
            return false;
        }

        // Check reminder days — trigger generation N days before the due date
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
            return null;
        }

        // Double check if already exists for this period
        $existing = Expense::where('parent_id', $template->id)
            ->whereYear('due_date', $dueDate->year)
            ->whereMonth('due_date', $dueDate->month)
            ->first();

        if ($existing) {
            $this->warn("Expense already exists for template {$template->id} for {$dueDate->format('F Y')}");
            return null;
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
            'status'            => ($dueDate->gte(Carbon::today())) ? 'upcoming' : 'pending',
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

        $this->info("Generated expense ID: {$expense->id} for '{$template->expense_name}' due on: {$dueDate->format('Y-m-d')}");

        return $expense;
    }
}
