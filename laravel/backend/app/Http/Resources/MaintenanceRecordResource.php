<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MaintenanceRecord;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MaintenanceRecord
 */
class MaintenanceRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'description' => $this->description,
            'category' => $this->category,

            'priority' => $this->priority instanceof BackedEnum ? $this->priority->value : (string) $this->priority,
            'status' => $this->status instanceof BackedEnum ? $this->status->value : (string) $this->status,

            'assigned_to' => $this->assigned_to,
            // Decimal string, never a float.
            'cost' => $this->cost === null ? null : (string) $this->cost,
            'cost_notes' => $this->cost_notes,
            'vendor_name' => $this->vendor_name,
            'vendor_phone' => $this->vendor_phone,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'completed_date' => $this->completed_date?->toDateString(),
            'notes' => $this->notes,

            'property_id' => $this->property_id !== null ? (int) $this->property_id : null,
            'property_name' => $this->whenLoaded('property', fn () => $this->property?->name),
            'house_id' => $this->house_id !== null ? (int) $this->house_id : null,
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),
            'tenant_id' => $this->tenant_id !== null ? (int) $this->tenant_id : null,
            'tenant_name' => $this->whenLoaded('renter', fn () => $this->renter?->name),

            'recipient_ids' => $this->recipientIds(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}