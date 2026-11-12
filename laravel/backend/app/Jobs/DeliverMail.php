<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\EmailLogStatus;
use App\Mail\RenderedMail;
use App\Models\EmailLog;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Delivers one queued message and records the outcome.
 *
 * The payload is just the delivery row's id: `email_logs` already holds the
 * recipient, subject and rendered body, so the log doubles as the queue entry.
 * There is deliberately no second `email_queue` table -- two stores for one
 * message is two sources of truth, and the legacy app had both.
 *
 * Retries re-run this job against the SAME stored body, so a retry can never
 * render differently from what the recipient was originally promised.
 */
class DeliverMail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Give up after this many attempts; the row is marked failed and left visible. */
    public int $tries = 3;

    /**
     * Backoff in seconds. Spaced out so a mail server that is briefly down is
     * not hammered, and so a rate-limited provider gets room to recover.
     *
     * @var list<int>
     */
    public array $backoff = [30, 120, 600];

    public function __construct(
        public readonly int $logId,
        public readonly int $ownerId,
    ) {}

    public function handle(): void
    {
        $this->withinOwner(function (): void {
            $log = EmailLog::query()->find($this->logId);

            // Already delivered or given up on: a duplicate dispatch (double
            // click, retry after a manual retry) must not send a second copy.
            if ($log === null || $log->status !== EmailLogStatus::Pending) {
                return;
            }

            // A scheduled message that is somehow picked up early waits its turn
            // rather than arriving before the reminder was meant to.
            if ($log->sent_at !== null && $log->sent_at->isFuture()) {
                $this->release($log->sent_at->diffInSeconds(now()));

                return;
            }

            Mail::to($log->to_email, $log->to_name)
                ->send(new RenderedMail((string) $log->subject, (string) $log->body));

            $log->forceFill([
                'status' => EmailLogStatus::Sent,
                'error' => null,
                'sent_at' => CarbonImmutable::now(),
            ])->save();
        });
    }

    /**
     * Called once the job has exhausted its retries.
     *
     * Without this the row would sit on 'pending' forever, which reads as
     * "still on its way" rather than "it failed and nobody was told".
     */
    public function failed(?Throwable $e): void
    {
        $this->withinOwner(function () use ($e): void {
            EmailLog::query()
                ->whereKey($this->logId)
                ->update([
                    'status' => EmailLogStatus::Failed->value,
                    'error' => $e?->getMessage() ?? 'Delivery failed after all attempts.',
                ]);
        });
    }

    /**
     * @param  callable(): void  $callback
     */
    private function withinOwner(callable $callback): void
    {
        $previous = TenantContext::id();

        TenantContext::set($this->ownerId);

        try {
            $callback();
        } finally {
            TenantContext::set($previous);
        }
    }
}
