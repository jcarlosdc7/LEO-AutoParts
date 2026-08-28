<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class CashSession extends Model
{
    protected $fillable = [
        'cash_register_id', 'user_id', 'opening_operation_id', 'closing_operation_id',
        'opening_amount', 'expected_amount',
        'closing_amount', 'difference', 'status', 'opening_notes',
        'closing_notes', 'opened_at', 'closed_at', 'closed_by',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2', 'expected_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2', 'difference' => 'decimal:2',
        'opened_at' => 'datetime', 'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (CashSession $session): void {
            if ($session->getOriginal('status') === 'closed') {
                throw new LogicException('Una sesión de caja cerrada no puede modificarse.');
            }

            $openingFields = ['cash_register_id', 'user_id', 'opening_operation_id', 'opening_amount', 'opening_notes', 'opened_at'];
            if (array_intersect(array_keys($session->getDirty()), $openingFields)) {
                throw new LogicException('Los datos originales de apertura no pueden modificarse.');
            }
        });

        static::deleting(fn () => throw new LogicException('Las sesiones de caja son historial y no pueden eliminarse.'));
    }

    public function register()
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function closingUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function counts()
    {
        return $this->hasMany(CashCount::class);
    }

    public function openingCount()
    {
        return $this->hasOne(CashCount::class)->where('type', 'OPENING');
    }

    public function closingCount()
    {
        return $this->hasOne(CashCount::class)->where('type', 'CLOSING');
    }
}
