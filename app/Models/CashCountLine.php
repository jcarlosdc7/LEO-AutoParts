<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class CashCountLine extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['cash_count_id', 'cash_denomination_id', 'quantity', 'subtotal'];

    protected $casts = ['quantity' => 'integer', 'subtotal' => 'decimal:2'];

    public function cashCount()
    {
        return $this->belongsTo(CashCount::class, 'cash_count_id');
    }

    public function denomination()
    {
        return $this->belongsTo(CashDenomination::class, 'cash_denomination_id');
    }
}
