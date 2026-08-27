<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['number', 'sale_id', 'sale_return_id', 'issued_at', 'currency', 'subtotal', 'tax', 'total', 'reason', 'status', 'created_by'];

    protected $casts = ['issued_at' => 'datetime', 'subtotal' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class);
    }
}
