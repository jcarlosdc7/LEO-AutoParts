<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'user_id', 'cash_session_id', 'total', 'amount', 'change',
        'sale_date', 'payment_method_id', 'status', 'void_reason', 'voided_by', 'voided_at',
    ];

    protected $casts = [
        'total' => 'decimal:2', 'amount' => 'decimal:2', 'change' => 'decimal:2',
        'sale_date' => 'datetime', 'voided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (Sale $sale): void {
            $immutableAttributes = [
                'customer_id', 'user_id', 'cash_session_id', 'total', 'amount',
                'change', 'sale_date', 'payment_method_id',
            ];

            if (array_intersect(array_keys($sale->getDirty()), $immutableAttributes)) {
                throw new LogicException('Los datos financieros originales de una venta no pueden modificarse.');
            }
        });

        static::deleting(function (): never {
            throw new LogicException('Las ventas contabilizadas deben anularse, nunca eliminarse.');
        });
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

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }
}
