<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BillItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentAllocation>
 *
 * Each row records that `amount` of `payment` settled one `bill_item`. This is
 * the ledger every financial figure is derived from.
 *
 * NOTE: payment_allocations has no owner_id and no updated_at; tenancy is
 * scoped THROUGH the payment.
 */
class PaymentAllocationFactory extends Factory
{
    protected $model = PaymentAllocation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'category' => 'Rent',
            // Decimal string, never a float.
            'amount' => '0.00',
        ];
    }

    public function split(Payment $payment, BillItem $item, string $amount, string $category = 'Rent'): static
    {
        return $this->state(fn (): array => [
            'payment_id' => $payment->getKey(),
            'bill_item_id' => $item->getKey(),
            'category' => $category,
            'amount' => $amount,
        ]);
    }
}