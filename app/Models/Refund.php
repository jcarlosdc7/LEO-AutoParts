<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['sale_return_id', 'sale_id', 'payment_method_id', 'sale_payment_id', 'cash_session_id', 'amount', 'reference', 'status', 'processed_by', 'processed_at'];

    protected $casts = ['amount' => 'decimal:2', 'processed_at' => 'datetime'];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cashSession()
    {
        return $this->belongsTo(CashSession::class);
    }
}
