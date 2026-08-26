<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dni_ruc', 'name', 'legal_name', 'email',
        'phone', 'address', 'city', 'customer_type_id', 'status', 'is_active',
    ];

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
