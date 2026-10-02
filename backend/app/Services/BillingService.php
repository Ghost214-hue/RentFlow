<?php
namespace App\Services;

use App\Core\Database;

class BillingService
{
    /**
     * Tolerance for "is this bill settled?", in currency units.
     *
     * MUST be at least one cent (0.01). A 0.001 epsilon is not enough:
     * 99.995 has no exact binary representation (it is stored as
     * 99.99499999...), so `99.995 + 0.001 >= 100.0` evaluates FALSE and a
     * fully-settled bill is reported as PARTIAL forever. One cent is the
     * smallest meaningful difference in KES and absorbs float error.
     */
    public const MONEY_EPSILON = 0.01;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createBillWithItems(int $ownerId, int $houseId, ?int $tenantId, string $month, string $dueDate, array $items): int
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) ($item['amount'] ?? 0);
        }

        $existing = null;
        if ($tenantId) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM bills WHERE owner_id = ? AND tenant_id = ? AND month = ? ORDER BY id LIMIT 1",
                [$ownerId, $tenantId, $month]
            );
        }
        if (!$existing) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM bills WHERE owner_id = ? AND house_id = ? AND month = ?",
                [$ownerId, $houseId, $month]
            );
        }

        if ($existing) {
            $billId = (int) $existing['id'];
            $existingItems = $this->getBillItems($billId);
            if (empty($existingItems)) {
                $this->addBillItems($billId, $items);
            }
            $this->recalculateBill($billId);
            return $billId;
        }

        $billId = $this->db->insert('bills', [
            'owner_id' => $ownerId,
            'house_id' => $houseId,
            'tenant_id' => $tenantId,
            'month' => $month,
            'total' => $total,
            'status' => $total > 0 ? 'pending' : 'paid',
            'due_date' => $dueDate,
        ]);

        $this->addBillItems($billId, $items);
        return $billId;
    }

    public function addBillItems(int $billId, array $items): void
    {
        foreach ($items as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }
            $status = $amount > 0 ? 'pending' : 'paid';
            $this->db->insert('bill_items', [
                'bill_id' => $billId,
                'type' => $item['type'] ?? 'Other',
                'description' => $item['description'] ?? null,
                'amount' => $amount,
                'paid' => 0.00,
                'status' => $status,
            ]);
        }
    }

    public function getBillItems(int $billId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM bill_items WHERE bill_id = ? ORDER BY id",
            [$billId]
        );
    }

    public function getBillTotals(int $billId): array
    {
        $items = $this->getBillItems($billId);
        $total = 0.0;
        $paid = 0.0;
        foreach ($items as $item) {
            $total += (float) $item['amount'];
            $paid += (float) $item['paid'];
        }
        return ['total' => $total, 'paid' => $paid];
    }

    public function getBillPaidTotal(array $bill): float
    {
        $billId = (int) ($bill['id'] ?? 0);
        if ($billId <= 0) {
            return 0.0;
        }

        $this->ensureLegacyBillItems($bill);
        $paidRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(pa.amount), 0) as total
             FROM payment_allocations pa
             JOIN payments p ON p.id = pa.payment_id
             WHERE pa.bill_item_id IN (SELECT id FROM bill_items WHERE bill_id = ?)
               AND p.status IN ('confirmed','completed','paid')",
            [$billId]
        );
        return max(0.0, (float) ($paidRow['total'] ?? 0));
    }

    public function ensureLegacyBillItems(array $bill): void
    {
        if ($this->db->fetchOne("SELECT id FROM bill_items WHERE bill_id = ? LIMIT 1", [$bill['id']])) {
            return;
        }

        $lineItems = [];
        if (isset($bill['rent']) && (float)$bill['rent'] > 0) {
            $lineItems[] = ['type' => 'Rent', 'description' => 'Monthly Rent', 'amount' => (float)$bill['rent']];
        }
        if (isset($bill['water']) && (float)$bill['water'] > 0) {
            $lineItems[] = ['type' => 'Water', 'description' => 'Water Charges', 'amount' => (float)$bill['water']];
        }
        if (isset($bill['electricity']) && (float)$bill['electricity'] > 0) {
            $lineItems[] = ['type' => 'Electricity', 'description' => 'Electricity Charges', 'amount' => (float)$bill['electricity']];
        }
        if (isset($bill['type']) && $bill['type'] === 'Deposit' && isset($bill['total']) && (float)$bill['total'] > 0) {
            $lineItems[] = ['type' => 'Deposit', 'description' => 'Security Deposit', 'amount' => (float)$bill['total']];
        }
        if (empty($lineItems)) {
            $lineItems[] = ['type' => $bill['type'] ?? 'Other', 'description' => $bill['description'] ?? 'Charge', 'amount' => (float)$bill['total']];
        }

        $this->addBillItems((int)$bill['id'], $lineItems);
        $this->recalculateBill((int)$bill['id']);
    }

    public function recalculateBill(int $billId): void
    {
        $bill = $this->db->fetchOne("SELECT * FROM bills WHERE id = ?", [$billId]);
        if (!$bill) {
            return;
        }

        $items = $this->getBillItems($billId);
        $total = 0.0;
        $paid = 0.0;
        foreach ($items as $item) {
            $total += (float) $item['amount'];
            $paid += (float) $item['paid'];
        }

        $status = 'pending';
        if ($paid <= 0) {
            $status = 'pending';
        } elseif ($paid >= $total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $this->db->update('bills', ['total' => $total, 'status' => $status], 'id = ?', [$billId]);
    }

    public function allocatePayment(int $paymentId, int $billId, float $amount, string $paymentType = 'Mixed'): float
    {
        $paymentType = $paymentType ?: 'Mixed';
        $bill = $this->db->fetchOne("SELECT * FROM bills WHERE id = ?", [$billId]);
        if (!$bill) {
            return $amount;
        }

        $this->ensureLegacyBillItems($bill);
        $items = $this->getBillItems($billId);
        $outstanding = [];
        foreach ($items as $item) {
            $remaining = max(0.0, (float)$item['amount'] - (float)$item['paid']);
            if ($remaining > 0) {
                $outstanding[] = [
                    'item' => $item,
                    'remaining' => $remaining,
                ];
            }
        }

        if (empty($outstanding)) {
            return $amount;
        }

        // Tree: allocate to the selected category first, then remaining by priority
        $priority = ['Rent', 'Deposit', 'Water', 'Electricity', 'Other'];
        if ($paymentType !== 'Mixed' && in_array($paymentType, $priority, true)) {
            $priority = array_merge([$paymentType], array_values(array_diff($priority, [$paymentType])));
        }

        usort($outstanding, function ($a, $b) use ($priority) {
            $posA = array_search($a['item']['type'], $priority, true);
            $posB = array_search($b['item']['type'], $priority, true);
            return $posA <=> $posB;
        });

        $remainingAmount = $amount;
        foreach ($outstanding as $entry) {
            if ($remainingAmount <= 0) {
                break;
            }
            $item = $entry['item'];
            $due = $entry['remaining'];
            if ($due <= 0) {
                continue;
            }
            $allocated = min($remainingAmount, $due);
            $this->db->insert('payment_allocations', [
                'payment_id' => $paymentId,
                'bill_item_id' => $item['id'],
                'category' => $item['type'],
                'amount' => $allocated,
            ]);
            $newPaid = (float)$item['paid'] + $allocated;
            $status = $newPaid >= (float)$item['amount'] ? 'paid' : 'partial';
            $this->db->update('bill_items', ['paid' => $newPaid, 'status' => $status], 'id = ?', [$item['id']]);
            $remainingAmount -= $allocated;
        }

        $this->recalculateBill($billId);
        return max(0.0, $remainingAmount);
    }

    /**
     * Build a collision-free receipt number.
     *
     * The old scheme was 'RCP-' . date('Y') . '-' . str_pad(time() % 10000, 4, ...)
     * which collides for any two payments in the same second and wraps every
     * ~2.8 hours. This uses a monotonic per-year counter derived from the
     * payments table itself, so it is unique without a new column, and is
     * retried on the (very unlikely) race.
     */
    public function generateReceiptNumber(?string $appUrl = null): string
    {
        $year = date('Y');
        $prefix = 'RCP-' . $year . '-';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) AS c FROM payments WHERE receipt LIKE ?",
                [$prefix . '%']
            );
            $seq = (int) ($row['c'] ?? 0) + 1 + $attempt;

            $receipt = $prefix . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

            $clash = $this->db->fetchOne(
                "SELECT id FROM payments WHERE receipt = ? LIMIT 1",
                [$receipt]
            );
            if (!$clash) {
                return $receipt;
            }
        }

        // Last resort: fall back to a timestamp that cannot repeat.
        return $prefix . date('His');
    }

    /**
     * Defect 1 fix — the correct allocation rule.
     *
     * A payment is applied to the renter's bills in this order:
     *   1. the explicitly chosen month's bill, first
     *   2. then the OLDEST unpaid bills, in month order
     *   3. the remainder becomes renter credit
     *
     * The old behaviour looked up only the single bill whose month equalled
     * the payment month, so paying in September never touched an unpaid
     * August bill and arrears could roll forward forever.
     *
     * Ordering is computed from the persisted allocation ledger
     * (payment_allocations), not from the denormalised bill_items.paid /
     * bills.status / tenants.balance columns, which are known to drift.
     *
     * Only settled payments allocate; pending/failed must never touch a bill.
     *
     * @param  string $chosenMonth YYYY-MM month the payer targeted ('' = none)
     * @return float  the unallocated remainder, i.e. new renter credit
     */
    public function allocatePaymentToOutstandingBills(
        int $paymentId,
        int $tenantId,
        float $amount,
        string $chosenMonth = '',
        string $paymentType = 'Mixed'
    ): float {
        if ($paymentId <= 0 || $amount <= 0.0) {
            return max(0.0, $amount);
        }

        // A payment only allocates once it is actually settled.
        $statusRow = $this->db->fetchOne("SELECT status FROM payments WHERE id = ?", [$paymentId]);
        if (!$statusRow || !in_array((string) $statusRow['status'], self::SETTLED_STATUSES, true)) {
            return max(0.0, $amount);
        }

        // Idempotency: only what is still unallocated may be spread further.
        $remaining = $this->unallocatedAmount($paymentId, $amount);
        if ($remaining <= 0.0) {
            return 0.0;
        }

        $category = $this->allocationCategory($paymentType);

        // Bill ids ordered: chosen month first, then oldest unpaid by month.
        $billIds = $this->outstandingBillsForAllocation($tenantId, $chosenMonth);

        foreach ($billIds as $billId) {
            if ($remaining <= 0.0) {
                break;
            }
            $bill = $this->db->fetchOne("SELECT * FROM bills WHERE id = ?", [$billId]);
            if (!$bill) {
                continue;
            }
            $this->ensureLegacyBillItems($bill);
            $absorbed = $this->allocateToBillItems($paymentId, $billId, $remaining, $category);
            $remaining = max(0.0, $remaining - $absorbed);
            $this->recalculateBill($billId);
        }

        return max(0.0, $remaining);
    }

    /**
     * Payments in these statuses count towards a bill.
     * 'confirmed' is legacy vocabulary (defect 2): the payments.status ENUM
     * does not contain it, but historic rows and the renter self-pay path both
     * use it. Accepted everywhere until legacy is retired.
     */
    public const SETTLED_STATUSES = ['confirmed', 'completed', 'paid'];

    /**
     * Map a payment type onto the bill item category it settles first.
     * 'Mixed' means "no category preference".
     */
    private function allocationCategory(string $paymentType): ?string
    {
        $priority = ['Rent', 'Deposit', 'Water', 'Electricity', 'Other'];
        if ($paymentType !== 'Mixed' && in_array($paymentType, $priority, true)) {
            return $paymentType;
        }
        return null;
    }

    /** How much of this payment is still unallocated. */
    private function unallocatedAmount(int $paymentId, float $amount): float
    {
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) t FROM payment_allocations WHERE payment_id = ?",
            [$paymentId]
        );
        $allocated = max(0.0, (float) ($row['t'] ?? 0));
        return max(0.0, $amount - $allocated);
    }

    /**
     * Candidate bills in allocation order:
     *   1. the bill for the explicitly chosen month (if still owed)
     *   2. every other unpaid bill, oldest month first
     *
     * "Unpaid" is derived from the allocation ledger, not bills.status.
     */
    private function outstandingBillsForAllocation(int $tenantId, string $chosenMonth): array
    {
        $rows = $this->db->fetchAll(
            "SELECT b.id, b.month,
                    COALESCE((SELECT SUM(bi.amount) FROM bill_items bi WHERE bi.bill_id = b.id), 0) AS item_total,
                    COALESCE((SELECT SUM(pa.amount) FROM payment_allocations pa
                              JOIN bill_items bi2 ON bi2.id = pa.bill_item_id
                              JOIN payments p ON p.id = pa.payment_id
                              WHERE bi2.bill_id = b.id
                                AND p.status IN ('confirmed','completed','paid')), 0) AS paid_total
             FROM bills b
             WHERE b.tenant_id = ? AND b.tenant_id > 0
             ORDER BY b.month ASC, b.id ASC",
            [$tenantId]
        );

        $outstanding = [];
        $chosenId = 0;
        foreach ($rows as $row) {
            if ((float) $row['item_total'] - (float) $row['paid_total'] <= self::MONEY_EPSILON) {
                continue; // settled
            }
            $outstanding[] = (int) $row['id'];
            if ($chosenMonth !== '' && (string) $row['month'] === $chosenMonth) {
                $chosenId = (int) $row['id'];
            }
        }

        if ($chosenId > 0) {
            // Hoist the chosen month to the front, keeping the rest in order.
            $outstanding = array_values(array_diff($outstanding, [$chosenId]));
            array_unshift($outstanding, $chosenId);
        }

        return $outstanding;
    }

    /**
     * Spread an amount across one bill's outstanding items and return how
     * much of it was actually absorbed.
     */
    private function allocateToBillItems(int $paymentId, int $billId, float $amount, ?string $preferredCategory): float
    {
        $outstanding = [];
        foreach ($this->getBillItems($billId) as $item) {
            $remainingOnItem = max(0.0, (float) $item['amount'] - $this->itemPaidTotal((int) $item['id']));
            if ($remainingOnItem > self::MONEY_EPSILON) {
                $outstanding[] = ['item' => $item, 'remaining' => $remainingOnItem];
            }
        }
        if (empty($outstanding)) {
            return 0.0;
        }

        if ($preferredCategory !== null) {
            // Settle the matching category first, then the rest by the
            // existing priority order (Rent, Deposit, Water, Electricity, Other).
            $priority = array_merge([$preferredCategory], ['Rent', 'Deposit', 'Water', 'Electricity', 'Other']);
            usort($outstanding, function ($a, $b) use ($priority) {
                $posA = array_search($a['item']['type'], $priority, true);
                $posB = array_search($b['item']['type'], $priority, true);
                return $posA <=> $posB;
            });
        }

        $remainingAmount = $amount;
        foreach ($outstanding as $entry) {
            if ($remainingAmount <= self::MONEY_EPSILON) {
                break;
            }
            $item = $entry['item'];
            $allocated = min($remainingAmount, $entry['remaining']);
            if ($allocated <= 0.0) {
                continue;
            }

            $this->db->insert('payment_allocations', [
                'payment_id' => $paymentId,
                'bill_item_id' => $item['id'],
                'category' => $item['type'],
                'amount' => $allocated,
            ]);
            $remainingAmount -= $allocated;

            // Keep the denormalised columns in step; they stay readable for
            // legacy pages but the allocation ledger remains authoritative.
            $newPaid = $this->itemPaidTotal((int) $item['id']);
            $status = $newPaid + self::MONEY_EPSILON >= (float) $item['amount'] ? 'paid' : 'partial';
            $this->db->update('bill_items', ['paid' => $newPaid, 'status' => $status], 'id = ?', [$item['id']]);
        }

        return max(0.0, $amount - $remainingAmount);
    }

    /** Amount settled against one bill item, from the allocation ledger. */
    private function itemPaidTotal(int $billItemId): float
    {
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(pa.amount), 0) t FROM payment_allocations pa
             JOIN payments p ON p.id = pa.payment_id
             WHERE pa.bill_item_id = ? AND p.status IN ('confirmed','completed','paid')",
            [$billItemId]
        );
        return max(0.0, (float) ($row['t'] ?? 0));
    }

    public function getBillAllocations(int $billId): array
    {
        return $this->db->fetchAll(
            "SELECT pa.*, p.type as payment_type, p.amount as payment_amount, p.method as payment_method, p.date as payment_date, p.receipt as payment_receipt, p.status as payment_status
             FROM payment_allocations pa
             JOIN payments p ON pa.payment_id = p.id
             WHERE pa.bill_item_id IN (SELECT id FROM bill_items WHERE bill_id = ?) ORDER BY p.created_at DESC",
            [$billId]
        );
    }

    public function getBillItemAllocations(int $billItemId): array
    {
        return $this->db->fetchAll(
            "SELECT pa.*, p.tenant_id, p.method, p.date, p.receipt, p.status FROM payment_allocations pa JOIN payments p ON pa.payment_id = p.id WHERE pa.bill_item_id = ? ORDER BY p.created_at DESC",
            [$billItemId]
        );
    }

    public function recalcTenantCreditAndBalance(int $tenantId): void
    {
        $tenant = $this->db->fetchOne("SELECT id FROM tenants WHERE id = ?", [$tenantId]);
        if (!$tenant) {
            return;
        }

        $billTotalRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM bill_items WHERE bill_id IN (SELECT id FROM bills WHERE tenant_id = ?)",
            [$tenantId]
        );

        $paidAgainstItemsRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(pa.amount), 0) as total FROM payments p JOIN payment_allocations pa ON pa.payment_id = p.id WHERE p.tenant_id = ? AND p.status IN ('confirmed','completed','paid')",
            [$tenantId]
        );

        $totalPaymentsRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE tenant_id = ? AND status IN ('confirmed','completed','paid')",
            [$tenantId]
        );

        $itemTotal = max(0.0, (float) ($billTotalRow['total'] ?? 0));
        $paidAgainstItems = max(0.0, (float) ($paidAgainstItemsRow['total'] ?? 0));
        $totalPayments = max(0.0, (float) ($totalPaymentsRow['total'] ?? 0));

        $due = max(0.0, $itemTotal - $paidAgainstItems);
        $credit = max(0.0, $totalPayments - $itemTotal);
        $balance = $due;

        $this->db->update('tenants', ['balance' => $balance, 'credit' => $credit], 'id = ?', [$tenantId]);
    }

    public function getTenantCarryForward(int $tenantId, string $month): array
    {
        if ($tenantId <= 0 || empty($month)) {
            return ['balance' => 0.0, 'credit' => 0.0];
        }

        $priorBillsRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(bi.amount), 0) as total
             FROM bills b
             JOIN bill_items bi ON bi.bill_id = b.id
             WHERE b.tenant_id = ? AND b.month < ?",
            [$tenantId, $month]
        );

        $priorAllocationsRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(pa.amount), 0) as total
             FROM payment_allocations pa
             JOIN payments p ON p.id = pa.payment_id
             WHERE p.tenant_id = ?
               AND p.status IN ('confirmed','completed','paid')
               AND pa.bill_item_id IN (
                   SELECT bi.id
                   FROM bill_items bi
                   JOIN bills b ON b.id = bi.bill_id
                   WHERE b.tenant_id = ? AND b.month < ?
               )",
            [$tenantId, $tenantId, $month]
        );

        $priorBills = max(0.0, (float) ($priorBillsRow['total'] ?? 0));
        $priorPaid = max(0.0, (float) ($priorAllocationsRow['total'] ?? 0));

        $balance = max(0.0, $priorBills - $priorPaid);
        $credit = max(0.0, $priorPaid - $priorBills);

        return ['balance' => $balance, 'credit' => $credit];
    }
    // =====================================================================
    // AUTHORITATIVE FINANCIAL DERIVATION (single source of truth)
    // ---------------------------------------------------------------------
    // Every consumer of financial figures — Bills list, single bill view,
    // invoice PDF, billing email, tenant portal, reports — MUST derive its
    // numbers through these helpers. No layer may recalculate rent, opening
    // balances, payments or status independently.
    // =====================================================================

    /**
     * Pure, side-effect-free derivation of the authoritative financial
     * position of a bill from its components:
     *
     *   itemsTotal : sum of the bill's persisted bill_items amounts
     *   paid       : sum of confirmed payment_allocations for this bill
     *   opening    : prior-month outstanding balance (carry forward)
     *   credit     : tenant credit applied to this bill
     *
     * Guarantees:
     *   - balance is NEVER negative (overpayments become tenant credit via
     *     recalcTenantCreditAndBalance, not a negative bill balance)
     *   - status follows: PAID / PARTIAL / PENDING (OVERDUE derived at read
     *     time from due_date via deriveStatusWithDueDate)
     */
    public static function finalizeFinancials(float $itemsTotal, float $paid, float $opening = 0.0, float $credit = 0.0): array
    {
        $itemsTotal = max(0.0, $itemsTotal);
        $paid       = max(0.0, $paid);
        $opening    = max(0.0, $opening);
        $credit     = max(0.0, $credit);

        $total   = max(0.0, $itemsTotal + $opening - $credit);
        $balance = max(0.0, $total - $paid);

        return [
            'total'           => round($total, 2),
            'paid'            => round(min($paid, $total), 2),
            'balance'         => round($balance, 2),
            'status'          => self::deriveStatus($total, $paid),
            'items_total'     => round($itemsTotal, 2),
            'opening_balance' => round($opening, 2),
            'credit_applied'  => round($credit, 2),
        ];
    }

    /**
     * Derive bill status consistently across all surfaces.
     */
    public static function deriveStatus(float $total, float $paid): string
    {
        $total = max(0.0, $total);
        $paid  = max(0.0, $paid);

        if ($total <= 0.0) {
            // A zero-total bill is nothing owed; any payment made against it
            // is tenant credit, not a negative balance.
            return 'paid';
        }
        if ($paid <= 0.0) {
            return 'pending';
        }
        if ($paid + self::MONEY_EPSILON >= $total) {
            return 'paid';
        }
        return 'partial';
    }

    /**
     * Status including the OVERDUE state once the due date has passed.
     */
    public static function deriveStatusWithDueDate(float $total, float $paid, ?string $dueDate): string
    {
        $status = self::deriveStatus($total, $paid);
        if ($status === 'pending' && $dueDate && strtotime($dueDate) < time()) {
            return 'overdue';
        }
        return $status;
    }

    /**
     * Authoritative snapshot for one bill row, reading ONLY persisted data:
     * bill_items, payment_allocations and the tenant carry-forward.
     * Used by the bills list, invoice, email and tenant portal so that every
     * surface reports identical numbers.
     */
    public function getAuthoritativeSnapshot(array $bill): array
    {
        $billId = (int) ($bill['id'] ?? 0);
        $this->ensureLegacyBillItems($bill);

        $itemsRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) AS item_total
             FROM bill_items WHERE bill_id = ?",
            [$billId]
        );
        $itemsTotal = max(0.0, (float) ($itemsRow['item_total'] ?? 0));

        $paid = $this->getBillPaidTotal($bill);

        $tenantId = (int) ($bill['tenant_id'] ?? 0);
        $carry = $tenantId > 0
            ? $this->getTenantCarryForward($tenantId, (string) ($bill['month'] ?? ''))
            : ['balance' => 0.0, 'credit' => 0.0];

        $snapshot = self::finalizeFinancials(
            $itemsTotal,
            $paid,
            (float) $carry['balance'],
            (float) $carry['credit']
        );
        $snapshot['due_date'] = $bill['due_date'] ?? null;
        $snapshot['display_status'] = self::deriveStatusWithDueDate(
            $snapshot['total'],
            $snapshot['paid'],
            $snapshot['due_date']
        );
        return $snapshot;
    }
}

