<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'stock_before', 'stock_after', 'reference_type', 'reference_id', 'notes'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
