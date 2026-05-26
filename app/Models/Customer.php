<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
	use HasFactory;

	protected $fillable = [
		'dni_ruc', 'name', 'legal_name', 'email', 
		'phone', 'address', 'city', 'customer_type_id', 'status'
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
