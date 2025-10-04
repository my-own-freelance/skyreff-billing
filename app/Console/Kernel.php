<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // // $schedule->command('inspire')->hourly();

        // // Generate invoice setiap awal bulan
        // $schedule->command('billing:generate-invoices')->monthlyOn(1, '00:00');

        // // Cek expired invoice tiap hari jam 1 pagi
        // $schedule->command('billing:check-expired-invoices')->dailyAt('01:00');

        if (app()->environment('production')) {
            // Production: jalan tiap 3 jam sekali
            $schedule->command('invoices:generate')->cron('0 */3 * * *');
            $schedule->command('invoice:check-expired')->cron('0 */3 * * *');
        } else {
            // Development: jalan tiap 1 menit
            $schedule->command('invoices:generate')->everyMinute();
            $schedule->command('invoice:check-expired')->everyMinute();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
