<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = ['cash_session_id', 'user_id', 'type', 'amount', 'reason', 'notes', 'reference_type', 'reference_id'];
    protected $casts = ['amount' => 'decimal:2'];
    public function session() { return $this->belongsTo(CashSession::class, 'cash_session_id'); }
}
