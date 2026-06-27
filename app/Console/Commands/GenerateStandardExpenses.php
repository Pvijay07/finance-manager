<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExpenseService;

class GenerateStandardExpenses extends Command
{
    protected $signature = 'expenses:generate-standard';
    protected $description = 'Generate standard monthly expenses';

    public function handle()
    {
        $this->info('Generating standard expenses...');

        $today = \Carbon\Carbon::today();
        $generatedCount = 0;
        $errorCount = 0;

        // Get all active standard expenses (templates)
        $templates = \App\Models\Expense::where('source', 'standard')
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

    private function shouldGenerateExpense($template, $today)
    {
        if (empty($template->frequency) || empty($template->due_day)) {
            return false;
        }

        $dueDate = $this->calculateDueDate($template, $today);

        if ($dueDate->isFuture() || $dueDate->isToday()) {
            $reminderDays = $template->reminder_days ?? 0;
            $reminderDate = $dueDate->copy()->subDays($reminderDays);

            return $today->isSameDay($reminderDate);
        }

        return false;
    }

    private function calculateDueDate($template, $today)
    {
        $currentYear = $today->year;
        $currentMonth = $today->month;

        switch ($template->frequency) {
            case 'monthly':
                return \Carbon\Carbon::create($currentYear, $currentMonth, $template->due_day);
            case 'quarterly':
                $quarter = ceil($currentMonth / 3);
                $quarterMonth = ($quarter * 3);
                return \Carbon\Carbon::create($currentYear, $quarterMonth, $template->due_day);
            case 'yearly':
                $dueMonth = $template->due_month ?? 1;
                return \Carbon\Carbon::create($currentYear, $dueMonth, $template->due_day);
            default:
                throw new \Exception("Unknown frequency: {$template->frequency}");
        }
    }

    private function generateExpenseFromTemplate($template, $today)
    {
        $dueDate = $this->calculateDueDate($template, $today);

        // Check if already exists for this period
        $existing = \App\Models\Expense::where('parent_id', $template->id)
            ->whereYear('due_date', $dueDate->year)
            ->whereMonth('due_date', $dueDate->month)
            ->first();

        if ($existing) {
            $this->warn("Expense already exists for template {$template->id} for {$dueDate->format('F Y')}");
            return;
        }

        $expense = \App\Models\Expense::create([
            'company_id' => $template->company_id,
            'category_id' => $template->category_id,
            'parent_id' => $template->id,
            'source' => 'auto_generated',
            'expense_name' => $template->expense_name,
            'party_name' => $template->party_name,
            'planned_amount' => $template->planned_amount,
            'actual_amount' => $template->planned_amount,
            'balance_amount' => $template->planned_amount,
            'paid_amount' => 0,
            'due_date' => $dueDate,
            'status' => 'pending',
            'tax_type' => $template->tax_type,
            'tax_percentage' => $template->tax_percentage,
            'tax_amount' => $template->tax_amount,
            'apply_tax' => $template->apply_tax,
            'notes' => "Auto-generated standard expense",
            'is_recurring' => 0,
            'is_active' => 1,
            'created_by' => $template->created_by
        ]);

        if ($template->taxes) {
            foreach ($template->taxes as $tax) {
                $expense->taxes()->create([
                    'tax_type' => $tax->tax_type,
                    'tax_percentage' => $tax->tax_percentage,
                    'tax_amount' => $tax->tax_amount,
                ]);
            }
        }

        $this->info("Generated expense ID: {$expense->id} due on: {$dueDate->format('Y-m-d')}");
    }
}
