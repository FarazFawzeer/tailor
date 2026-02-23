<?php

namespace App\Models\Tailoring;

use Illuminate\Database\Eloquent\Model;

class ItemMeasurementValue extends Model
{
    protected $fillable = [
        'item_measurement_set_id',
        'measurement_field_id',
        'value',
    ];

    public function set()
    {
        return $this->belongsTo(ItemMeasurementSet::class, 'item_measurement_set_id');
    }

    public function field()
    {
        return $this->belongsTo(\App\Models\MeasurementField::class, 'measurement_field_id');
    }
}