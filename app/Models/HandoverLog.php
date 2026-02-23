<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandoverLog extends Model
{
    protected $fillable = [
        'job_batch_item_id',
        'from_stage_id',
        'to_stage_id',
        'qty',
        'handed_over_by',
        'received_by',
        'notes',
        'handover_at',
    ];

    protected $casts = [
        'handover_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\JobBatchItem::class, 'job_batch_item_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(\App\Models\WorkflowStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(\App\Models\WorkflowStage::class, 'to_stage_id');
    }

    public function handedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'handed_over_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }
}