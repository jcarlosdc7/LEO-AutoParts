<?php

namespace App\Models;

use App\Models\Concerns\ImmutableFinancialRecord;
use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
    use ImmutableFinancialRecord;

    protected $fillable = ['credit_note_id', 'sale_detail_id', 'description', 'quantity', 'unit_price', 'subtotal', 'tax', 'total'];

    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2'];
}
