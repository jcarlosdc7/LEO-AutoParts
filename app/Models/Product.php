<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'brand',
        'model', 'supplier_id', 'category_id',
        'min_stock', 'price', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if ((int) ($product->getAttribute('stock') ?? 0) !== 0) {
                throw new LogicException('El saldo inicial debe registrarse mediante InventoryService.');
            }
        });

        static::updating(function (Product $product): void {
            if ($product->isDirty('stock')) {
                throw new LogicException('El stock solo puede cambiar mediante InventoryService.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Los productos deben archivarse, nunca eliminarse físicamente.');
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
