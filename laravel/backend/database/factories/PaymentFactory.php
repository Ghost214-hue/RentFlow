<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\House;
use App\Models\Payment;
use App\Models\Renter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 *
 * NOTE: the column is `date`, not `paid_at`.
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'month' => now()->format('Y-m'),
            // Decimal string, never a float.
            'amount' => '0.00',
            'type' => 'Rent',
            'method' => 'M-Pesa',
            'date' => now()->toDateString(),
            'status' => 'completed',
            'tenant_confirmed' => false,
        ];
    }

    public function forTenant(Renter $renter): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $renter->getKey(),
            'house_id' => $renter->house_id,
        ]);
    }

    public function ofAmount(string $amount): static
    {
        return $this->state(fn (): array => ['amount' => $amount]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => 'pending']);
    }
}