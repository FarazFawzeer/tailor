<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireAgreementItem extends Model
{
    protected $fillable = [
        'hire_agreement_id',
        'hire_item_id',
        'hire_price',
        'deposit_amount',
    ];

    protected $casts = [
        'hire_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function agreement()
    {
        return $this->belongsTo(HireAgreement::class, 'hire_agreement_id');
    }

    public function item()
    {
        return $this->belongsTo(HireItem::class, 'hire_item_id');
    }
}