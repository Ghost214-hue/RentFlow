<?php

declare(strict_types=1);

namespace App\Billing;

use App\Enums\BillStatus;
use App\Support\Amount;

/**
 * Immutable value object holding one bill's authoritative figures.
 *
 * @see BillSnapshotBuilder for how these are derived.
 */
final class BillSnapshotData
{
    public function __construct(
        public readonly int $billId,
        public readonly \Brick\Money\Money $itemsTotal,
        public readonly \Brick\Money\Money $openingBalance,
        public readonly \Brick\Money\Money $creditApplied,
        public readonly \Brick\Money\Money $total,
        public readonly \Brick\Money\Money $paid,
        public readonly \Brick\Money\Money $balance,
        public readonly BillStatus $status,
        public readonly BillStatus $displayStatus,
        public readonly ?\DateTimeInterface $dueDate,
    ) {}

    /**
     * Wire representation. Money is a STRING so TypeScript can only format
     * it, never compute with it.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'items_total' => Amount::toString($this->itemsTotal),
            'opening_balance' => Amount::toString($this->openingBalance),
            'credit_applied' => Amount::toString($this->creditApplied),
            'total' => Amount::toString($this->total),
            'paid' => Amount::toString($this->paid),
            'balance' => Amount::toString($this->balance),
            'status' => $this->status->value,
            'display_status' => $this->displayStatus->value,
            'due_date' => $this->dueDate?->format('Y-m-d'),
        ];
    }
}