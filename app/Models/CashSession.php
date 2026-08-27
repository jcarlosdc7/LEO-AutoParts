<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = [
        'cash_register_id', 'user_id', 'opening_amount', 'expected_amount',
        'closing_amount', 'difference', 'status', 'opening_notes',
        'closing_notes', 'opened_at', 'closed_at', 'closed_by',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2', 'expected_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2', 'difference' => 'decimal:2',
        'opened_at' => 'datetime', 'closed_at' => 'datetime',
    ];

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
}
