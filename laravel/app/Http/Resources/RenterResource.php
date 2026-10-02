<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Renter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Renter
 */
class RenterResource extends JsonResource
{
    /**
     * Every money field is a STRING. `balance` and `credit` are denormalised
     * caches that drift (defect 5); BillSnapshot is authoritative for bills.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => $this->email,
            'phone' => (string) $this->phone,

            'id_type' => (string) $this->id_type,
            'id_number' => $this->id_number,

            'status' => (string) $this->status,
            'profile_picture' => $this->profile_picture,

            'property_id' => $this->property_id !== null ? (int) $this->property_id : null,
            'property_name' => $this->whenLoaded('property', fn () => $this->property?->name),

            'house_id' => $this->house_id !== null ? (int) $this->house_id : null,
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),

            // `tenants` has no rent column. The renter's rent is the UNIT's rent
            // (houses.rent), which is also what drives bill generation. So it
            // is read from the loaded house rather than from the renter.
            'rent' => (string) ($this->house?->rent ?? '0.00'),

            // Deposit and balances live on the renter.
            'deposit' => (string) $this->deposit,
            'balance' => (string) $this->balance,
            'credit' => (string) $this->credit,

            'lease_start' => $this->lease_start?->toDateString(),
            'lease_end' => $this->lease_end?->toDateString(),

            'next_of_kin_name' => $this->next_of_kin_name,
            'next_of_kin_phone' => $this->next_of_kin_phone,
            'next_of_kin_email' => $this->next_of_kin_email,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}