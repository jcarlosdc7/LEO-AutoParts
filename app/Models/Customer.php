<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni_ruc', 'name', 'legal_name', 'email',
        'phone', 'address', 'city', 'customer_type_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
