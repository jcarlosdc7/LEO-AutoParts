<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['purchase_number', 'supplier_id', 'user_id', 'purchase_date', 'status', 'subtotal', 'discount_total', 'tax_total', 'total', 'amount_paid', 'balance', 'due_date', 'notes', 'cancelled_at'];

    protected function casts(): array
    {
        return ['purchase_date' => 'datetime', 'due_date' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
