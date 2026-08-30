<?php

namespace App\Support;

use InvalidArgumentException;

final class Decimal
{
    public const STORAGE_SCALE = 2;

    public const UNIT_SCALE = 4;

    public const CALCULATION_SCALE = 6;

    public static function parse(
        string|int $value,
        int $scale = self::STORAGE_SCALE,
        bool $allowNegative = false,
        string $field = 'amount',
    ): string {
        $value = trim((string) $value);
        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException("{$field} must be a plain decimal number.");
        }

        $negative = str_starts_with($value, '-');
        if ($negative && ! $allowNegative) {
            throw new InvalidArgumentException("{$field} cannot be negative.");
        }

        $absolute = ltrim($value, '-');
        [$whole, $fraction] = array_pad(explode('.', $absolute, 2), 2, '');
        $whole = ltrim($whole, '0');
        $whole = $whole === '' ? '0' : $whole;
        if (strlen($whole) > (int) config('money.max_integer_digits', 14)) {
            throw new InvalidArgumentException("{$field} exceeds the supported range.");
        }
        if (strlen($fraction) > $scale) {
            throw new InvalidArgumentException("{$field} supports at most {$scale} decimal places.");
        }

        $normalized = $whole.($scale > 0 ? '.'.str_pad($fraction, $scale, '0') : '');

        return $negative && bccomp($normalized, self::zero($scale), $scale) !== 0
            ? '-'.$normalized
            : $normalized;
    }

    public static function round(string|int $value, int $scale = self::STORAGE_SCALE): string
    {
        $raw = self::canonical($value);
        $negative = str_starts_with($raw, '-');
        $absolute = ltrim($raw, '-');
        [$whole, $fraction] = array_pad(explode('.', $absolute, 2), 2, '');

        if (strlen($fraction) <= $scale) {
            $rounded = self::parse($absolute, $scale, false, 'decimal');
        } else {
            $truncated = $whole.($scale > 0 ? '.'.substr($fraction, 0, $scale) : '');
            $quantum = $scale > 0 ? '0.'.str_repeat('0', $scale - 1).'1' : '1';
            $rounded = $fraction[$scale] >= '5'
                ? bcadd($truncated, $quantum, $scale)
                : bcadd($truncated, '0', $scale);
        }

        $signed = $negative && bccomp($rounded, self::zero($scale), $scale) !== 0
            ? '-'.$rounded
            : $rounded;

        return self::parse($signed, $scale, true, 'decimal');
    }

    public static function add(string|int $left, string|int $right, int $scale = self::STORAGE_SCALE): string
    {
        return self::parse(bcadd(self::canonical($left), self::canonical($right), $scale), $scale, true, 'decimal');
    }

    public static function subtract(string|int $left, string|int $right, int $scale = self::STORAGE_SCALE): string
    {
        return self::parse(bcsub(self::canonical($left), self::canonical($right), $scale), $scale, true, 'decimal');
    }

    public static function multiply(
        string|int $left,
        string|int $right,
        int $scale = self::CALCULATION_SCALE,
    ): string {
        return self::parse(bcmul(self::canonical($left), self::canonical($right), $scale), $scale, true, 'decimal');
    }

    public static function compare(string|int $left, string|int $right, int $scale = self::CALCULATION_SCALE): int
    {
        return bccomp(self::canonical($left), self::canonical($right), $scale);
    }

    public static function percentage(string|int $value, string|int $maximum, int $scale = 2): string
    {
        if (self::compare($maximum, '0', self::CALCULATION_SCALE) <= 0) {
            return self::zero($scale);
        }

        $percentage = bcdiv(
            self::multiply($value, '100', self::CALCULATION_SCALE),
            self::canonical($maximum),
            $scale,
        );

        return self::compare($percentage, '100', $scale) > 0 ? self::parse('100', $scale) : $percentage;
    }

    public static function maximum(iterable $values, int $scale = self::CALCULATION_SCALE): string
    {
        $maximum = self::zero($scale);
        foreach ($values as $value) {
            if (self::compare((string) $value, $maximum, $scale) > 0) {
                $maximum = self::round((string) $value, $scale);
            }
        }

        return $maximum;
    }

    public static function format(string|int $value, int $scale = self::STORAGE_SCALE): string
    {
        $normalized = self::round($value, $scale);
        $negative = str_starts_with($normalized, '-');
        [$whole, $fraction] = array_pad(explode('.', ltrim($normalized, '-'), 2), 2, '');
        $groups = [];
        while (strlen($whole) > 3) {
            array_unshift($groups, substr($whole, -3));
            $whole = substr($whole, 0, -3);
        }
        array_unshift($groups, $whole);

        return ($negative ? '-' : '').implode(',', $groups).($scale > 0 ? '.'.$fraction : '');
    }

    public static function zero(int $scale = self::STORAGE_SCALE): string
    {
        return '0'.($scale > 0 ? '.'.str_repeat('0', $scale) : '');
    }

    private static function canonical(string|int $value): string
    {
        $value = trim((string) $value);
        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException('Value must be a plain decimal number.');
        }

        $absolute = ltrim($value, '-');
        [$whole] = explode('.', $absolute, 2);
        if (strlen(ltrim($whole, '0') ?: '0') > (int) config('money.max_integer_digits', 14)) {
            throw new InvalidArgumentException('Decimal value exceeds the supported range.');
        }

        return $value;
    }
}
