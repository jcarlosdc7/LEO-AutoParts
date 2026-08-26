<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'stock_before', 'stock_after',
        'unit_cost', 'reference_type', 'reference_id', 'user_id', 'note', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'unit_cost' => 'decimal:2'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
