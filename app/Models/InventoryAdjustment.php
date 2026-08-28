<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = [
        'operation_id', 'product_id', 'warehouse_id', 'type', 'quantity',
        'reason', 'created_by', 'occurred_at',
    ];

    protected $casts = ['occurred_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movement()
    {
        return $this->morphOne(StockMovement::class, 'reference');
    }
}
