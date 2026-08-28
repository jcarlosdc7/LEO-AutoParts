<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_session_id', 'user_id', 'operation_id', 'type', 'amount', 'reason',
        'reference', 'notes', 'approved_by', 'approved_at', 'reference_type', 'reference_id',
    ];

    protected $casts = ['amount' => 'decimal:2', 'approved_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Los movimientos de caja no pueden modificarse.'));
        static::deleting(fn () => throw new LogicException('Los movimientos de caja no pueden eliminarse.'));
    }

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
