<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = ['sale_id', 'payment_method_id', 'cash_session_id', 'amount', 'received_amount', 'change_amount', 'reference'];
    protected $casts = ['amount' => 'decimal:2', 'received_amount' => 'decimal:2', 'change_amount' => 'decimal:2'];
    public function sale() { return $this->belongsTo(Sale::class); }
}
