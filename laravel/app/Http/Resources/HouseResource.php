<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin House
 */
class HouseResource extends JsonResource
{
    /**
     * Money is emitted as a STRING, never a number. The TypeScript side types
     * these as `string` and only formats them — no arithmetic in the browser.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'unit' => (string) $this->unit,
            'type' => (string) $this->type,
            'rent' => (string) $this->rent,
            'status' => (string) $this->status,

            'property_id' => (int) $this->property_id,
            'property_name' => $this->whenLoaded(
                'property',
                fn () => $this->property?->name,
            ),

            'tenant_id' => $this->tenant_id !== null ? (int) $this->tenant_id : null,
            'tenant_name' => $this->whenLoaded(
                'renter',
                fn () => $this->renter?->name,
            ),

            'water_meter' => $this->water_meter !== null ? (string) $this->water_meter : null,
            'elec_meter' => $this->elec_meter !== null ? (string) $this->elec_meter : null,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}