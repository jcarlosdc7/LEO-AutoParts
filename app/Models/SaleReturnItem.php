<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['sale_return_id', 'sale_detail_id', 'product_id', 'quantity', 'unit_price', 'refund_amount', 'condition', 'restock', 'reason'];

    protected $casts = ['unit_price' => 'decimal:2', 'refund_amount' => 'decimal:2', 'restock' => 'boolean'];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleDetail()
    {
        return $this->belongsTo(SaleDetail::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
