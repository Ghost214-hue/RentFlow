<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Caretaker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caretaker>
 *
 * assigned_properties is a CSV string in the live schema.
 */
class CaretakerFactory extends Factory
{
    protected $model = Caretaker::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            'password' => 'password',
        ];
    }

    /** @param array<int, int> $propertyIds */
    public function assignedTo(array $propertyIds): static
    {
        return $this->state(fn (): array => [
            'assigned_properties' => implode(',', array_map('intval', $propertyIds)),
        ]);
    }
}