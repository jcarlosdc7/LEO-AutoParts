<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class CashDenomination extends Model
{
    protected $fillable = ['currency_code', 'value', 'type', 'is_active', 'sort_order'];

    protected $casts = ['value' => 'decimal:2', 'is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::deleting(fn () => throw new LogicException('Las denominaciones históricas no pueden eliminarse.'));
    }
}
