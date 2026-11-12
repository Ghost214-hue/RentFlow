<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SmsLogStatus;
use App\Models\SmsLog;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The single way this application sends SMS.
 *
 * THE ONE RULE: YOU CANNOT SEND SMS WITHOUT LOGGING.
 *
 * Every SMS is queued via DeliverSms job with a PENDING delivery row created
 * first, so the audit trail exists even if the HTTPS gateway is down.
 *
 * SMS failures NEVER propagate to the caller. A bill generation or payment
 * recording must still succeed when the SMS gateway is down; the failure
 * belongs in the delivery log.
 */
final class SmsService
{
    public function __construct(private readonly SmsTemplateRenderer $renderer) {}

    /**
     * Queue an SMS for delivery via HTTPS gateway.
     *
     * @param  array<string, mixed>  $variables
     * @return SmsLog The PENDING delivery row
     */
    public function send(
        string $template,
        int $ownerId,
        string $toPhone,
        string $toName,
        array $variables = [],
        ?CarbonImmutable $scheduledAt = null,
    ): SmsLog {
        return $this->withinOwner($ownerId, function () use (
            $template,
            $ownerId,
            $toPhone,
            $toName,
            $variables,
            $scheduledAt
        ): SmsLog {
            $rendered = $this->renderer->render($template, $variables);

            $log = SmsLog::query()->create([
                'to_phone' => $toPhone,
                'to_name' => $toName,
                'message' => $rendered['body'],
                'status' => SmsLogStatus::Pending,
                'sent_at' => $scheduledAt ?? CarbonImmutable::now(),
            ]);

            DeliverSms::dispatch($log->getKey(), $ownerId)
                ->delay($scheduledAt ?? CarbonImmutable::now());

            return $log;
        });
    }

    /**
     * Queue an SMS, swallowing every failure.
     *
     * This is what domain code should call. An SMS problem must not roll back
     * the bill generation or payment that triggered it.
     */
    public function trySend(
        string $template,
        int $ownerId,
        string $toPhone,
        string $toName,
        array $variables = [],
        ?CarbonImmutable $scheduledAt = null,
    ): ?SmsLog {
        try {
            return $this->send($template, $ownerId, $toPhone, $toName, $variables, $scheduledAt);
        } catch (Throwable $e) {
            Log::warning('SMS could not be queued.', [
                'template' => $template,
                'owner_id' => $ownerId,
                'to' => $toPhone,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Re-queue an already-rendered SMS message, e.g. from the delivery log.
     */
    public function retry(SmsLog $log): void
    {
        $ownerId = (int) $log->owner_id;

        $this->withinOwner($ownerId, function () use ($log, $ownerId): void {
            $log->forceFill([
                'status' => SmsLogStatus::Pending,
                'error' => null,
            ])->save();

            DeliverSms::dispatch($log->getKey(), $ownerId)->delay(CarbonImmutable::now());
        });
    }

    private function withinOwner(int $ownerId, callable $callback): mixed
    {
        $previous = TenantContext::id();

        TenantContext::set($ownerId);

        try {
            return $callback();
        } finally {
            TenantContext::set($previous);
        }
    }
}