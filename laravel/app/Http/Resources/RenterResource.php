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
            /*
             * `status` is cast to the RenterStatus enum and the phone/id_type
             * columns are nullable, so casting straight to string fatals with
             * "could not be converted to string". Enum cases expose ->value.
             */
            'phone' => $this->phone === null ? null : (string) $this->phone,

            'id_type' => $this->id_type === null ? null : (string) $this->id_type,
            'id_number' => $this->id_number,

            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'profile_picture' => $this->profile_picture,

            'property_id' => $this->property_id !== null ? (int) $this->property_id : null,
            'property_name' => $this->whenLoaded('property', fn () => $this->property?->name),

            'house_id' => $this->house_id !== null ? (int) $this->house_id : null,
            'house_unit' => $this->whenLoaded('house', fn () => $this->house?->unit),

            // `tenants` has no rent column. The renter's rent is the UNIT's rent
            // (houses.rent), which is also what drives bill generation. So it
            // is read from the loaded house rather than from the renter.
            'rent' => (string) ($this->house?->rent ?? '0.00'),

            // Deposit and balances live on the renter. These columns are nullable, so
            // a null would fatal on a bare (string) cast; '0.00' is the safe
            // equivalent because Amount::of() would read null as zero anyway.
            'deposit' => (string) ($this->deposit ?? '0.00'),
            'balance' => (string) ($this->balance ?? '0.00'),
            'credit' => (string) ($this->credit ?? '0.00'),

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