<?php

namespace App\Models\Tailoring;

use Illuminate\Database\Eloquent\Model;

class ItemMeasurementSet extends Model
{
    protected $fillable = [
        'job_batch_item_id',
        'piece_no',
        'captured_by',
        'notes',
    ];

    public function values()
    {
        return $this->hasMany(ItemMeasurementValue::class, 'item_measurement_set_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\JobBatchItem::class, 'job_batch_item_id');
    }
}