<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Money handling
|--------------------------------------------------------------------------
|
| Money must never become a float. These tests pin the Amount helpers so a
| future refactor cannot quietly reintroduce float arithmetic.
|
*/

use App\Support\Amount;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

it('parses a decimal string exactly', function (): void {
    $money = Amount::of('1234.56');

    expect($money->getAmount()->toFloat())->toBe(1234.56)
        ->and(Amount::toString($money))->toBe('1234.56');
});

it('treats null as zero rather than throwing', function (): void {
    expect(Amount::toString(Amount::of(null)))->toBe('0.00')
        ->and(Amount::toString(Amount::zero()))->toBe('0.00');
});

it('sums without floating point drift', function (): void {
    // 0.1 + 0.2 must be exactly 0.30, which float arithmetic gets wrong.
    $total = Amount::sum(['0.10', '0.20']);

    expect(Amount::toString($total))->toBe('0.30');
});

it('sums a long list without drift', function (): void {
    // Ten lots of 0.07 is exactly 0.70.
    $total = Amount::sum(array_fill(0, 10, '0.07'));

    expect(Amount::toString($total))->toBe('0.70');
});

it('computes an outstanding balance', function (): void {
    $owed = Amount::owed(Amount::of('50000.00'), Amount::of('17500.50'));

    expect(Amount::toString($owed))->toBe('32499.50');
});

it('never reports a negative outstanding balance', function (): void {
    // An overpayment must clamp to zero, not become a negative debt.
    $owed = Amount::owed(Amount::of('100.00'), Amount::of('250.00'));

    expect(Amount::toString($owed))->toBe('0.00');
});

it('rounds at the currency minor unit', function (): void {
    $rounded = Money::of(1000, 'KES')->plus(
        Money::of(1, 'KES')->multipliedBy(1, RoundingMode::HalfUp)
    );

    expect(Amount::toString($rounded))->toBe('1001.00');
});

it('compares amounts', function (): void {
    expect(Amount::equals(Amount::of('10.00'), Amount::of('10.0')))->toBeTrue()
        ->and(Amount::equals(Amount::of('10.00'), Amount::of('10.01')))->toBeFalse();
});

it('rejects a malformed money string', function (): void {
    expect(Amount::tryFromInput('not-money'))->toBeNull();
});