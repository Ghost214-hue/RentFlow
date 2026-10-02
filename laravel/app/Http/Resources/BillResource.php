<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Billing\BillSnapshotBuilder;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bill
 */
class BillResource extends JsonResource
{
    /**
     * Money is a STRING. The figures come from BillSnapshot, never from the
     * denormalised bills.total / bills.status columns, so this list, the
     * invoice, the email and the reports can never disagree.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Resolved from the container rather than injected in a constructor:
        // Laravel's Collection::make($resource) passes extra arguments here,
        // which a custom constructor would misinterpret.
        $snapshots = app(BillSnapshotBuilder::class);

        $snapshot = $snapshots->build($this->resource)->toArray();

        return [
            'id' => (int) $this->id,
            'month' => (string) $this->month,
            'due_date' => $snapshot['due_date'],

            // Authoritative figures.
            'amount' => $snapshot['total'],
            'paid' => $snapshot['paid'],
            'balance' => $snapshot['balance'],
            'status' => $snapshot['display_status'],
            'opening_balance' => $snapshot['opening_balance'],
            'credit_applied' => $snapshot['credit_applied'],

            'house_id' => (int) $this->house_id,
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),
            'property_name' => $this->whenLoaded('house.property', fn () => $this->house?->property?->name),

            'tenant_id' => $this->tenant_id !== null ? (int) $this->tenant_id : null,
            'tenant_name' => $this->whenLoaded('renter', fn () => $this->renter?->name),

            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => (int) $item->id,
                'type' => (string) $item->type,
                'description' => $item->description,
                'amount' => (string) $item->amount,
                'paid' => (string) $item->paid,
                'status' => (string) $item->status,
            ])->values()),
        ];
    }
}