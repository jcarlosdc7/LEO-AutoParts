<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    protected $fillable = ['opened_by', 'closed_by', 'opening_amount', 'expected_amount', 'closing_amount', 'difference', 'opened_at', 'closed_at', 'status', 'notes'];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime', 'closed_at' => 'datetime', 'opening_amount' => 'decimal:2', 'expected_amount' => 'decimal:2', 'closing_amount' => 'decimal:2', 'difference' => 'decimal:2'];
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
