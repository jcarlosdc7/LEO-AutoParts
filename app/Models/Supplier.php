<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
	use HasFactory;

	protected $casts = ['is_active' => 'boolean'];

	protected $fillable = ['name', 'contact', 'phone', 'address', 'is_active'];

	public function products()
	{
		return $this->hasMany(Product::class);
	}
}
