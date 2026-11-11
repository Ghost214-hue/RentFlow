<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Health check for the mail pipeline.
 *
 * The web UI shows one owner's delivery log, which is the right place to look
 * when a specific renter says they got nothing. This answers the other
 * question: is mail flowing AT ALL? A stopped queue worker leaves every row on
 * 'pending' and nothing anywhere looks broken until someone complains.
 *
 *     php artisan email:stats
 */
class EmailStats extends Command
{
    protected $signature = 'email:stats {--pending-hours=1 : Age at which pending mail counts as stalled}';

    protected $description = 'Summarise outbound email by delivery status and flag a stalled queue';

    public function handle(): int
    {
        $byStatus = DB::table('email_logs')
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $pending = (int) ($byStatus['pending'] ?? 0);
        $stalled = (int) DB::table('email_logs')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours((int) $this->option('pending-hours')))
            ->count();

        $this->components->twoColumnDetail('Sent', (string) ($byStatus['sent'] ?? 0));
        $this->components->twoColumnDetail('Pending', (string) $pending);
        $this->components->twoColumnDetail('Failed', (string) ($byStatus['failed'] ?? 0));
        $this->components->twoColumnDetail('Pending over '.$this->option('pending-hours').'h', (string) $stalled);

        $recentFailure = DB::table('email_logs')
            ->where('status', 'failed')
            ->orderByDesc('id')
            ->first(['to_email', 'subject', 'error', 'created_at']);

        if ($recentFailure !== null) {
            $this->newLine();
            $this->components->twoColumnDetail('Last failure', (string) $recentFailure->to_email);
            $this->components->twoColumnDetail('Subject', (string) $recentFailure->subject);
            $this->components->twoColumnDetail('Error', mb_substr((string) $recentFailure->error, 0, 120));
        }

        if ($stalled > 0) {
            $this->newLine();
            $this->components->warn(
                "Mail has been pending for over {$this->option('pending-hours')}h. Is the queue worker running?"
            );
            $this->components->bulletList(['php artisan queue:work --queue=default']);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
