<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MaintenanceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceRecord>
 *
 * NOTE: maintenance enums differ from complaints. status starts at 'pending'
 * (not 'open'), can be 'cancelled', and priority includes 'urgent' which
 * complaints does not have.
 */
class MaintenanceRecordFactory extends Factory
{
    protected $model = MaintenanceRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => 'General',
            'priority' => 'medium',
            'status' => 'pending',
            // Decimal string, never a float.
            'cost' => '0.00',
            'recipient_type' => 'individual',
        ];
    }

    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => 'urgent']);
    }

    public function forTenant(int $tenantId): static
    {
        return $this->state(fn (): array => ['tenant_id' => $tenantId]);
    }
}