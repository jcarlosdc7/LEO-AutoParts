<?php

namespace App\Support;

use InvalidArgumentException;
use Stringable;

final readonly class Money implements Stringable
{
    private function __construct(
        private string $amount,
        private string $currency,
    ) {}

    public static function parse(string|int $amount, ?string $currency = null): self
    {
        return new self(
            Decimal::parse($amount, Decimal::STORAGE_SCALE, false, 'money'),
            $currency ?? (string) config('money.currency', 'NIO'),
        );
    }

    public static function fromCalculation(string|int $amount, ?string $currency = null): self
    {
        return new self(
            Decimal::round($amount, Decimal::STORAGE_SCALE),
            $currency ?? (string) config('money.currency', 'NIO'),
        );
    }

    public static function fromUnitPrice(string|int $unitPrice, int $quantity, ?string $currency = null): self
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        $unitPrice = Decimal::parse($unitPrice, Decimal::UNIT_SCALE, false, 'unit price');

        return self::fromCalculation(
            Decimal::multiply($unitPrice, (string) $quantity, Decimal::CALCULATION_SCALE),
            $currency,
        );
    }

    public static function zero(?string $currency = null): self
    {
        return self::parse('0', $currency);
    }

    public function add(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self(Decimal::add($this->amount, $other->amount), $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self(Decimal::subtract($this->amount, $other->amount), $this->currency);
    }

    public function compare(self $other): int
    {
        $this->ensureSameCurrency($other);

        return Decimal::compare($this->amount, $other->amount, Decimal::STORAGE_SCALE);
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function __toString(): string
    {
        return $this->amount;
    }

    private function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Currency mismatch.');
        }
    }
}
