<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HireItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'hire_item_id',
        'size',
        'qty',
        'color',
        'hire_price',
        'deposit_amount',
        'is_active',
    ];

    public function item()
    {
        return $this->belongsTo(HireItem::class, 'hire_item_id');
    }
}