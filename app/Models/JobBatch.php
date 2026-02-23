<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobBatch extends Model
{
    protected $fillable = [
        'job_id',
        'batch_no',
        'batch_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'batch_date' => 'date',
        'due_date' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function items()
    {
        return $this->hasMany(JobBatchItem::class)->latest();
    }

    public static function nextBatchNo(int $jobId): string
    {
        $count = JobBatch::where('job_id', $jobId)->count();
        $next = $count + 1;
        return 'BATCH-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
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
}
