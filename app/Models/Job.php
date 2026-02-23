<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = [
        'job_no',
        'customer_id',
        'job_date',
        'due_date',
        'notes',
        'current_stage_id',
        'created_by',
    ];

    protected $casts = [
        'job_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function batches()
    {
        return $this->hasMany(JobBatch::class)->latest();
    }

    public function currentStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'current_stage_id');
    }

    protected static function booted()
    {
        static::creating(function ($job) {
            $lastId = (int) (Job::max('id') ?? 0);
            $next = $lastId + 1;
            $job->job_no = 'JOB-' . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
        });
    }


    public function delivery()
    {
        return $this->hasOne(\App\Models\Delivery::class, 'job_id');
    }

   
}
