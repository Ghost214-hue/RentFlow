<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\House;
use App\Models\Renter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'month' => now()->format('Y-m'),
            'type' => 'Rent',
            'description' => 'Monthly rent',
            'rent' => '50000.00',
            'water' => '0.00',
            'electricity' => '0.00',
            'total' => '50000.00',
            'status' => 'pending',
            'due_date' => now()->addDays(7)->toDateString(),
        ];
    }

    public function forTenant(Renter $renter): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $renter->getKey(),
            'house_id' => $renter->house_id,
        ]);
    }

    public function forMonth(string $month): static
    {
        return $this->state(fn (): array => ['month' => $month]);
    }

    /**
     * Create the bill together with its rent line and return both.
     *
     * BillSnapshot derives every figure from bill_items, so a bill with no
     * items totals zero. Tests need the item to allocate against.
     *
     * @return array{0: Bill, 1: BillItem}
     */
    public function createWithItem(array $attributes = []): array
    {
        $bill = $this->create($attributes);

        // The caller holds the tenant context, which BillItem's
        // owner-through-bill scope requires.
        $item = BillItem::query()->create([
            'bill_id' => $bill->getKey(),
            'type' => 'Rent',
            'description' => 'Monthly rent',
            'amount' => (string) ($attributes['rent'] ?? '50000.00'),
            'paid' => '0.00',
            'status' => 'pending',
        ]);

        return [$bill, $item];
    }
}