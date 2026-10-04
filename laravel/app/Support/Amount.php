<?php

declare(strict_types=1);

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

/**
 * Money handling for RentFlow.
 *
 * The currency is KES and every amount is DECIMAL(12,2). Money is NEVER a
 * float: floats silently lose cents (0.1 + 0.2 !== 0.3), which is exactly how
 * billing totals drift out of agreement between the bills list, the invoice
 * and the email. Brick\Money is arbitrary-precision throughout.
 */
final class Amount
{
    public const CURRENCY = 'KES';

    /** Scale for DECIMAL(12,2). */
    private const SCALE = 2;

    /**
     * Build Money from any decimal-ish input (string, int, float from a
     * legacy caller). Floats are converted via their string form, which is
     * the least-wrong option for data already computed upstream.
     */
    public static function of(string|int|float|null $amount): Money
    {
        if ($amount === null || $amount === '') {
            return self::zero();
        }

        $value = is_float($amount)
            ? number_format($amount, self::SCALE, '.', '')
            : (string) $amount;

        return Money::of(BigDecimal::of($value), self::CURRENCY);
    }

    public static function zero(): Money
    {
        return Money::of(BigDecimal::zero()->toScale(self::SCALE), self::CURRENCY);
    }

    /**
     * Parse a request-supplied amount strictly: rejects junk, rejects more
     * than two decimals, rejects negatives.
     */
    public static function tryFromInput(?string $amount): ?Money
    {
        if ($amount === null || trim($amount) === '') {
            return null;
        }

        $trimmed = trim($amount);

        if (preg_match('/^\d+(\.\d{1,2})?$/', $trimmed) !== 1) {
            return null;
        }

        return self::of($trimmed);
    }

    /**
     * Add, never subtract-and-compare. Use this for totals.
     */
    public static function sum(iterable $amounts): Money
    {
        $total = self::zero();

        foreach ($amounts as $amount) {
            $total = $total->plus(self::of($amount));
        }

        return $total;
    }

    /**
     * Clamp at zero. An overpayment is CREDIT, never negative debt â€” this is
     * the rule the legacy remediation established and every surface must obey.
     */
    public static function atLeastZero(Money $money): Money
    {
        return $money->isNegative() ? self::zero() : $money;
    }

    /**
     * What is still owed on this bill: total - paid, floored at zero.
     */
    public static function owed(Money $total, Money $paid): Money
    {
        return self::atLeastZero($total->minus($paid));
    }

    /** "6500.00" â€” the wire format, always two decimals. */
    public static function toString(Money $money): string
    {
        return $money->getAmount()->toScale(self::SCALE)->__toString();
    }

    /**
     * Absolute value. Brick\Money has no absoluteValue(), and the negative
     * case is exactly the one credit needs.
     */
    public static function absolute(Money $money): Money
    {
        return $money->isNegative() ? $money->multipliedBy(-1) : $money;
    }

    /** Comparison helper that never touches floats. */
    public static function equals(Money $a, Money $b): bool
    {
        return $a->compareTo($b) === 0;
    }

    /**
     * Mean of a money total over a count, e.g. average repair cost per job.
     *
     * A zero or negative count is 0.00 rather than a division error: an owner
     * with no completed repairs has no average, and an exception here would take
     * the whole reports page down.
     *
     * HALF_UP matches how an accountant would round a per-job figure, and keeps
     * the result exact rather than truncating cents.
     */
    public static function average(Money $total, int $count): Money
    {
        if ($count <= 0) {
            return self::zero();
        }

        return $total->dividedBy(BigDecimal::of($count), RoundingMode::HalfUp);
    }
}
