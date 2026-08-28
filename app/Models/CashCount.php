<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class CashCount extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = [
        'cash_session_id', 'operation_id', 'type', 'total', 'expected_amount',
        'difference', 'difference_reason', 'performed_by', 'performed_at',
    ];

    protected $casts = [
        'total' => 'decimal:2', 'expected_amount' => 'decimal:2',
        'difference' => 'decimal:2', 'performed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function lines()
    {
        return $this->hasMany(CashCountLine::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
