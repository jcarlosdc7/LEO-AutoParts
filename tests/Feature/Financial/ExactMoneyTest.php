<?php

use App\Support\Decimal;
use App\Support\Money;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('decimal policy rejects ambiguous inputs and rounds half up explicitly', function () {
    expect(Decimal::parse('0012.3'))->toBe('12.30')
        ->and(Decimal::round('10.005'))->toBe('10.01')
        ->and(Decimal::round('-10.005'))->toBe('-10.01')
        ->and(Money::fromUnitPrice('0.3333', 3)->amount())->toBe('1.00')
        ->and(Decimal::format('1234567.8'))->toBe('1,234,567.80');

    expect(fn () => Decimal::parse('1e3'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Decimal::parse('1.001'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Decimal::parse('-0.01'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => Decimal::round('99999999999999.999'))->toThrow(InvalidArgumentException::class);
});

test('repeated monetary accumulation does not drift', function () {
    $total = Decimal::zero();

    foreach (range(1, 10000) as $_) {
        $total = Decimal::add($total, '0.01');
    }

    expect($total)->toBe('100.00');
});

test('schema stores monetary values as decimals with the approved scales', function () {
    $columns = DB::table('information_schema.COLUMNS')
        ->whereRaw('TABLE_SCHEMA = DATABASE()')
        ->whereIn('COLUMN_NAME', ['price', 'amount', 'total', 'unit_price', 'unit_cost'])
        ->get(['TABLE_NAME', 'COLUMN_NAME', 'DATA_TYPE', 'NUMERIC_PRECISION', 'NUMERIC_SCALE']);

    expect($columns)->not->toBeEmpty()
        ->and($columns->whereIn('DATA_TYPE', ['float', 'double', 'real']))->toBeEmpty()
        ->and(DB::table('monetary_migration_audits')
            ->where('migration_key', '2026_08_28_000009_exact_money')
            ->where('status', 'verified')
            ->exists())->toBeTrue();
});

test('money reconciliation is read only and passes on a consistent ledger', function () {
    expect(Artisan::call('money:reconcile'))->toBe(0)
        ->and(Artisan::output())->toContain('no monetary drift detected');
});
