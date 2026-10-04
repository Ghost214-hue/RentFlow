<?php

declare(strict_types=1);

namespace App\Support;

use Brick\Money\Money;

/**
 * Percentage arithmetic for reports.
 *
 * A figure you show a tenant has to be stable: (1 / 3) * 100 is
 * 33.333333333333336 in binary floating point, so a report built on floats can
 * print a rate that changes in the last digit depending on how it was summed.
 * Everything here is integer arithmetic at a fixed scale.
 */
final class Rate
{
    /** Percentage scale: two decimals, so 100% is held as 10000. */
    private const SCALE = 100;

    /**
     * part/whole as a percentage string, e.g. 2/3 -> "66.67".
     *
     * A zero denominator is "0.00", never an error and never 100%: an owner with
     * no units has no occupancy rate, and reporting 100% would be a lie.
     *
     * The numerator is rounded up by half a denominator so the result is
     * correctly rounded rather than truncated (2/3 -> 66.67, not 66.66).
     */
    public static function percent(int $part, int $whole): string
    {
        if ($whole === 0) {
            return '0.00';
        }

        return number_format(
            \intdiv($part * 100 * self::SCALE + \intdiv($whole, 2), $whole) / self::SCALE,
            2,
        );
    }

    /**
     * Ratio of two amounts as a percentage, for collection and occupancy money.
     *
     * Money::getMinorAmount() applies the currency scale (2 dp); BigDecimal,
     * which Money::getAmount() returns, has no toMinorUnit().
     */
    public static function of(Money $part, Money $whole): string
    {
        return self::percent(
            (int) $part->getMinorAmount()->toInt(),
            (int) $whole->getMinorAmount()->toInt(),
        );
    }
}
