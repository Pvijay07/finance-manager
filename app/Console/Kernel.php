<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Generate standard expenses on 1st of each month
        $schedule->command('expenses:generate-standard')
                 ->monthlyOn(1, '00:05');

        // Send payment reminders daily at 9 AM
        $schedule->command('notifications:payment-reminders')
                 ->dailyAt('09:00');

        // Check for overdue payments daily
        $schedule->command('payments:check-overdue')
                 ->dailyAt('00:00');

        // Generate compliance tasks monthly
        $schedule->command('compliance:generate-tasks')
                 ->monthlyOn(1, '00:10');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
