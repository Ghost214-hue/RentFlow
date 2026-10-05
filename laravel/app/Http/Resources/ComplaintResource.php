<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Complaint
 */
class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'description' => $this->description,
            'category' => $this->category,

            // Enums are cast on the model; emit their backing value for JSON.
            'priority' => $this->priority instanceof \BackedEnum ? $this->priority->value : (string) $this->priority,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,

            'date' => $this->date?->toDateString(),
            'sender_role' => $this->sender_role,
            'recipient_type' => $this->recipient_type,
            'recipient_ids' => $this->recipientIds(),

            'property_id' => $this->property_id !== null ? (int) $this->property_id : null,
            'property_name' => $this->whenLoaded('property', fn () => $this->property?->name),
            'house_id' => $this->house_id !== null ? (int) $this->house_id : null,
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),
            'tenant_id' => $this->tenant_id !== null ? (int) $this->tenant_id : null,
            'tenant_name' => $this->whenLoaded('renter', fn () => $this->renter?->name),

            'timeline' => $this->timelineEntries(),
            'comments' => $this->comments,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}