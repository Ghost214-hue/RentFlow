<?php

use App\Console\Commands\SendRentReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Scheduled work.
 *
 * withoutOverlapping(): two runs at once would both pass the "has this been
 * sent?" check before either records it, and the renter would get the reminder
 * twice. The reminder command dedupes on (bill_id, days_before_due), so that
 * race is worth locking out rather than relying on luck.
 */
Schedule::command(SendRentReminders::class)
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->description('Email renters whose rent is unpaid and approaching its due date');

/*
 * A watchdog over the mail queue.
 *
 * The queue worker is what actually delivers mail, and a stopped worker is
 * invisible from the web UI: rows just sit on 'pending'. This reports how long
 * anything has been pending, which is the signal that the worker is down.
 */
Schedule::call(function (): void {
    $stale = DB::table('email_logs')
        ->where('status', 'pending')
        ->where('created_at', '<', now()->subHour())
        ->count();

    if ($stale > 0) {
        Log::warning('Email queue appears stalled.', ['pending_over_an_hour' => $stale]);
    }
})->hourly()->name('email-queue-watchdog')->description('Warn if mail has been pending for over an hour');
