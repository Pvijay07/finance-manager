<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExpenseService;

class GenerateStandardExpenses extends Command
{
    protected $signature = 'expenses:generate-standard';
    protected $description = 'Generate standard monthly expenses';

    public function handle(ExpenseService $expenseService)
    {
        $this->info('Generating standard expenses...');

        $expenseService->generateMonthlyExpenses();

        $this->info('Standard expenses generated successfully.');

        // Log the generation
        activity()
            ->causedByAnonymous()
            ->log('Standard expenses auto-generated for the month');
    }
}
