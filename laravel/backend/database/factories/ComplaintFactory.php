<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 *
 * NOTE: complaints.status has NO 'pending_approval' value in the live schema,
 * despite the legacy controller filtering on it.
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => 'General',
            'priority' => 'medium',
            'status' => 'open',
            'date' => now()->toDateString(),
            'sender_role' => 'tenant',
            'recipient_type' => 'individual',
        ];
    }

    public function fromTenant(int $tenantId): static
    {
        return $this->state(fn (): array => ['sender_role' => 'tenant', 'tenant_id' => $tenantId]);
    }

    /** A broadcast naming the given renter ids as recipients. */
    public function broadcastTo(array $renterIds): static
    {
        return $this->state(fn (): array => [
            'sender_role' => 'owner',
            'recipient_type' => 'all',
            'recipient_ids' => json_encode(array_values(array_map('intval', $renterIds))),
        ]);
    }
}