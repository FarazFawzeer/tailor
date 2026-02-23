<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementField extends Model
{
    protected $fillable = [
        'measurement_template_id',
        'label',
        'key',
        'unit',
        'input_type',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(MeasurementTemplate::class, 'measurement_template_id');
    }
}