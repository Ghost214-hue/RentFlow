<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as Kernel as BaseKernel;
use App\Console\Commands\SendMonthlyBills;
use App\Console\Commands\SendRentReminders;

/**
 * The Artisan kernel for the application.
 *
 * This class is responsible for scheduling background tasks, such as jobs and
 * scheduled consoles, for your application. It also registers the Artisan
 * commands provided by your application as well as any third-party packages.
 */
class Kernel extends BaseKernel
{
    /**
     * The Artisan commands registered with the application.
     *
     * @var array
     */
    protected $commands = [
        SendMonthlyBills::class,
        SendRentReminders::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Send monthly bills to all tenants on the 1st day of each month at 9:00 AM
        $schedule->command('send:monthly:bills')
            ->monthlyOn(1, '09:00')
            ->description('Send monthly bills to all tenants');

        // Send rent reminders daily at 9:00 AM (existing functionality)
        $schedule->command('rent:remind')
            ->dailyAt('09:00')
            ->description('Email renters whose rent is unpaid and approaching its due date');
    }
}