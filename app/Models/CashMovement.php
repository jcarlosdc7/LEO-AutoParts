<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = ['cash_session_id', 'type', 'amount', 'description', 'reference_type', 'reference_id', 'user_id', 'occurred_at'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
