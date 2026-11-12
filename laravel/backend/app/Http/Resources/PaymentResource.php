<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * Money is a STRING. `amount` is what the payer handed over;
     * `allocated` is what actually reached bill items (the rest is credit).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'receipt' => $this->receipt,
            'amount' => (string) $this->amount,
            // Cast to the ENUM's backing value: the model casts status to the
            // PaymentStatus enum, which cannot be stringified for JSON.
            'allocated' => (string) ($this->attributes['allocated'] ?? '0.00'),
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'type' => (string) $this->type,
            'method' => (string) $this->method,
            'month' => (string) $this->month,
            'date' => $this->date?->toDateString(),
            'description' => $this->description,

            'tenant_id' => $this->tenant_id !== null ? (int) $this->tenant_id : null,
            'tenant_name' => $this->whenLoaded('renter', fn () => $this->renter?->name),
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),

            'tenant_confirmed' => (bool) $this->tenant_confirmed,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}