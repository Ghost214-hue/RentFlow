<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\House;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<House>
 */
class HouseFactory extends Factory
{
    protected $model = House::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'unit' => 'Unit '.fake()->unique()->numberBetween(1, 9999),
            'type' => '1 Bedroom',
            'status' => 'vacant',
            // Money is a decimal string, never a float.
            'rent' => '45000.00',
            'water_meter' => 'WM-'.fake()->unique()->numerify('####'),
            'elec_meter' => 'EM-'.fake()->unique()->numerify('####'),
        ];
    }

    public function forProperty(Property $property): static
    {
        return $this->state(fn (): array => ['property_id' => $property->getKey()]);
    }

    public function occupied(): static
    {
        return $this->state(fn (): array => ['status' => 'occupied']);
    }
}