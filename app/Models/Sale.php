<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['invoice_number', 'customer_id', 'user_id', 'payment_method_id', 'sale_date', 'status', 'subtotal', 'discount_total', 'tax_total', 'total', 'amount', 'amount_received', 'change', 'balance', 'customer_name_snapshot', 'customer_document_snapshot', 'cancelled_at', 'cancelled_by', 'cancellation_reason'];

    protected function casts(): array
    {
        return ['sale_date' => 'datetime', 'cancelled_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'total' => 'decimal:2', 'amount_received' => 'decimal:2', 'balance' => 'decimal:2'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
