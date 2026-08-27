<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	use HasFactory;

	protected $fillable = [
		'code', 'name', 'description', 'brand', 
		'model', 'supplier_id', 'category_id', 'stock', 
		'min_stock', 'price', 'is_active'
	];

	protected $casts = [
		'price' => 'decimal:2',
		'is_active' => 'boolean',
	];

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}

	public function category()
	{
		return $this->belongsTo(Category::class);
	}

	public function saleDetails()
	{
		return $this->hasMany(SaleDetail::class);
	}
}
