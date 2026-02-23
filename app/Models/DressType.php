<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DressType extends Model
{
    protected $fillable = ['code', 'name', 'notes', 'is_active', 'diagram_front', 'diagram_back'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function measurementTemplates()
    {
        return $this->hasMany(MeasurementTemplate::class);
    }
}