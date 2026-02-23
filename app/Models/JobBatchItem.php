<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobBatchItem extends Model
{
    protected $fillable = [
        'job_batch_id',
        'dress_type_id',
        'measurement_template_id',
        'qty',
        'current_stage_id',
        'per_piece_measurement',
        'notes',
        'unit_price',
        'line_total',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'per_piece_measurement' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(JobBatch::class, 'job_batch_id');
    }


    public function measurementTemplate()
    {
        return $this->belongsTo(MeasurementTemplate::class);
    }


    public function jobBatch()
    {
        return $this->belongsTo(\App\Models\JobBatch::class, 'job_batch_id');
    }

    public function dressType()
    {
        return $this->belongsTo(\App\Models\DressType::class, 'dress_type_id');
    }


    public function stage()
    {
        return $this->belongsTo(\App\Models\WorkflowStage::class, 'current_stage_id');
    }

    public function handovers()
    {
        return $this->hasMany(\App\Models\HandoverLog::class, 'job_batch_item_id');
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->line_total = ((float)$item->qty) * ((float)$item->unit_price);
        });
    }

    public function parentItem()
    {
        return $this->belongsTo(\App\Models\JobBatchItem::class, 'parent_item_id');
    }

    public function childItems()
    {
        return $this->hasMany(\App\Models\JobBatchItem::class, 'parent_item_id');
    }
}
