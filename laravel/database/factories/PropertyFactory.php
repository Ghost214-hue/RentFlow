<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => 'Tower '.fake()->unique()->numberBetween(1, 9999),
            'address' => fake()->address(),
            'type' => 'Apartment Block',
            'units' => 10,
            'occupied' => 0,
            'rent' => '45000.00',
            'payment_method_type' => 'mobile_money',
            'mobile_money_number' => '0712345678',
        ];
    }

    public function forOwner(Owner $owner): static
    {
        return $this->state(fn (): array => ['owner_id' => $owner->getKey()]);
    }

    /** A property with a short, deterministic name for readable assertions. */
    public function named(string $name): static
    {
        return $this->state(fn (): array => ['name' => $name]);
    }
}