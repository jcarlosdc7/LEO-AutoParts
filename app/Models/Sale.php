<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
	use HasFactory;

	protected $fillable = ['customer_id', 'user_id', 'total', 'sale_date', 'payment_method_id', 'status'];

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
}

