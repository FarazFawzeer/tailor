<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireItem extends Model
{
    protected $fillable = [
        'item_code',
        'name',
        'category',
        'size',
        'color',
        'hire_price',
        'deposit_amount',
        'status',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hire_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function images()
    {
        return $this->hasMany(HireItemImage::class);
    }
}