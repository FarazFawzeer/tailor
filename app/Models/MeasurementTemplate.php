<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementTemplate extends Model
{
    protected $fillable = [
        'dress_type_id',
        'name',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dressType()
    {
        return $this->belongsTo(DressType::class);
    }

    public function fields()
    {
        return $this->hasMany(MeasurementField::class)->orderBy('sort_order');
    }
}