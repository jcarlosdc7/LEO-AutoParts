<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use ImmutableFinancialRecord;

    protected $fillable = ['sale_id', 'amount', 'payment_date', 'payment_method_id'];

    protected $casts = ['amount' => 'decimal:2', 'payment_date' => 'datetime'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
