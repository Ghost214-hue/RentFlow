<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TenancyTermination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenancyTermination>
 */
class TenancyTerminationFactory extends Factory
{
    protected $model = TenancyTermination::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'initiated_by' => 'tenant',
            'reason' => fake()->sentence(),
            // effective_date is NOT NULL in the live schema.
            'effective_date' => now()->addDays(30)->toDateString(),
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['status' => 'completed']);
    }

    public function initiatedByOwner(): static
    {
        return $this->state(fn (): array => ['initiated_by' => 'owner']);
    }
}