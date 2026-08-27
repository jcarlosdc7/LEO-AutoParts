<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['operation_id', 'sale_id', 'return_number', 'status', 'reason', 'notes', 'requested_by', 'authorized_by', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class);
    }
}
