<?php

namespace App\Models\Concerns;

use LogicException;

trait ImmutableFinancialRecord
{
    public static function bootImmutableFinancialRecord(): void
    {
        static::updating(function (): never {
            throw new LogicException('Los registros financieros históricos no pueden modificarse.');
        });

        static::deleting(function (): never {
            throw new LogicException('Los registros financieros históricos no pueden eliminarse.');
        });
    }
}
