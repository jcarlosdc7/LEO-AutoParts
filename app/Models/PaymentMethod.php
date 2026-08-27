<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'requires_reference', 'affects_cash_drawer', 'is_active',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'affects_cash_drawer' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
