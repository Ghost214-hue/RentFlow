<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\House;
use App\Models\Property;
use App\Models\Renter;
use Database\Factories\Concerns\HasTestPassword;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Renter>
 *
 * NOTE: `tenants` has NO `rent` column in the live schema. The renter's rent is
 * the UNIT's rent (houses.rent). Do not add 'rent' here: it will throw
 * "Unknown column 'rent'".
 */
class RenterFactory extends Factory
{
    /** @use HasTestPassword */
    use HasTestPassword;

    protected $model = Renter::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            'password' => 'password',
            'status' => 'active',
            // Decimal strings, never floats.
            'deposit' => '50000.00',
            'balance' => '0.00',
            'credit' => '0.00',
            'water_balance' => '0.00',
            'elec_balance' => '0.00',
        ];
    }

    public function forProperty(Property $property): static
    {
        return $this->state(fn (): array => ['property_id' => $property->getKey()]);
    }

    /**
     * Link this renter to a house.
     *
     * BOTH sides are written: tenants.house_id and houses.tenant_id. The
     * legacy schema stores the link in both places and code reads both --
     * House::renter() joins houses.tenant_id while HousePolicy checks the
     * renter's house_id. Setting only one leaves the unit detail page showing
     * a vacant unit for an occupied one.
     */
    public function inHouse(House $house): static
    {
        return $this->afterCreating(function (Renter $renter) use ($house): void {
            $house->forceFill(['tenant_id' => $renter->getKey()])->save();
        })->state(fn (): array => [
            'house_id' => $house->getKey(),
            'property_id' => $house->property_id,
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn (): array => ['status' => 'pending_termination']);
    }
}