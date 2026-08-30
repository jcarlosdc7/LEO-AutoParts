<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = [
        'product_id', 'warehouse_id', 'operation_key', 'user_id', 'type',
        'quantity', 'stock_before', 'stock_after', 'reference_type',
        'reference_id', 'notes', 'occurred_at', 'unit_cost', 'total_cost',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
