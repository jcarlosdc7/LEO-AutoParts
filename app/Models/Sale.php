<?php

namespace App\Models;

use App\Support\Decimal;
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

    public function cashMovements()
    {
        return $this->morphMany(CashMovement::class, 'reference');
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function getRefundedTotalAttribute(): string
    {
        return (string) $this->refunds()->where('status', 'completed')->sum('amount');
    }

    public function getNetEconomicValueAttribute(): string
    {
        return Decimal::subtract((string) $this->total, $this->refunded_total);
    }

    public function getEconomicStatusAttribute(): string
    {
        if ($this->status === 'voided') {
            return 'voided';
        }

        $details = $this->relationLoaded('saleDetails') ? $this->saleDetails : $this->saleDetails()->get();
        $returns = $this->relationLoaded('saleReturns')
            ? $this->saleReturns
            : $this->saleReturns()->with('items')->get();
        $originalQuantity = (int) $details->sum('quantity');
        $returnedQuantity = (int) $returns->where('status', 'completed')->sum(
            fn (SaleReturn $saleReturn): int => (int) $saleReturn->items->sum('quantity'),
        );

        if ($returnedQuantity === 0) {
            return 'completed';
        }

        return $returnedQuantity >= $originalQuantity ? 'fully_returned' : 'partially_returned';
    }
}
