<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SmsLogStatus;
use App\Models\SmsLog;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Delivers one queued SMS via the HTTPS gateway and records the outcome.
 *
 * The payload is just the delivery row's id: `sms_logs` already holds the
 * recipient and message body, so the log doubles as the queue entry.
 *
 * Retries re-run this job against the SAME stored body, so a retry can never
 * send a different message from what was originally promised.
 */
class DeliverSms implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        public readonly int $logId,
        public readonly int $ownerId,
    ) {}

    public function handle(): void
    {
        $this->withinOwner(function (): void {
            $log = SmsLog::query()->find($this->logId);

            if ($log === null || $log->status !== SmsLogStatus::Pending) {
                return;
            }

            if ($log->sent_at !== null && $log->sent_at->isFuture()) {
                $this->release($log->sent_at->diffInSeconds(now()));

                return;
            }

            $this->sendViaHttps($log);

            $log->forceFill([
                'status' => SmsLogStatus::Sent,
                'error' => null,
                'sent_at' => CarbonImmutable::now(),
            ])->save();
        });
    }

    public function failed(?Throwable $e): void
    {
        $this->withinOwner(function () use ($e): void {
            SmsLog::query()
                ->whereKey($this->logId)
                ->update([
                    'status' => SmsLogStatus::Failed->value,
                    'error' => $e?->getMessage() ?? 'SMS delivery failed after all attempts.',
                ]);
        });
    }

    private function sendViaHttps(SmsLog $log): void
    {
        $endpoint = config('sms.endpoint', env('SMS_ENDPOINT', ''));
        $apiKey = config('sms.api_key', env('SMS_API_KEY', ''));
        $senderId = config('sms.sender_id', env('SMS_SENDER_ID', 'RENTFLOW'));

        if ($endpoint === '') {
            throw new \RuntimeException('SMS_ENDPOINT is not configured.');
        }

        $response = Http::timeout(15)->post($endpoint, [
            'api_key' => $apiKey,
            'to' => $log->to_phone,
            'from' => $senderId,
            'message' => $log->message,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'SMS gateway returned HTTP '.$response->status().': '.$response->body()
            );
        }
    }

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