<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Property;
use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Property
 */
class PropertyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /*
         * Unit counts are recomputed from the house rows rather than read from
         * properties.units / properties.occupied. Those columns are caches that
         * drift whenever a unit is added or vacated outside the property form.
         *
         * House::status is a plain string column (it is NOT cast to an enum), so
         * it is compared directly rather than via ->value.
         */
        $houses = $this->whenLoaded('houses');
        $houseCount = $houses === null ? null : $houses->count();
        $occupiedCount = $houses === null
            ? null
            : $houses->filter(
                fn ($h) => ($h->status instanceof BackedEnum ? $h->status->value : (string) $h->status) === 'occupied'
            )->count();

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'address' => $this->address,
            'type' => $this->type,
            'image' => $this->image,

            // Decimal string, never a float.
            'rent' => (string) ($this->rent ?? '0.00'),

            // Cached columns, shown for reference only.
            'units_recorded' => $this->units !== null ? (int) $this->units : 0,
            'occupied_recorded' => $this->occupied !== null ? (int) $this->occupied : 0,

            // Authoritative counts derived from houses.
            'units' => $houseCount ?? ($this->units !== null ? (int) $this->units : 0),
            'occupied' => $occupiedCount ?? ($this->occupied !== null ? (int) $this->occupied : 0),

            'payment_method_type' => $this->payment_method_type,
            'paybill_number' => $this->paybill_number,
            'paybill_account' => $this->paybill_account,
            'till_number' => $this->till_number,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'bank_branch' => $this->bank_branch,
            'mobile_money_number' => $this->mobile_money_number,

            'caretaker_id' => $this->caretaker_id !== null ? (int) $this->caretaker_id : null,
            'caretaker_name' => $this->whenLoaded('caretaker', fn () => $this->caretaker?->name),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}