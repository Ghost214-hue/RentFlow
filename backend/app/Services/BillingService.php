<?php
namespace App\Services;

use App\Core\Database;

class BillingService
{
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
}
