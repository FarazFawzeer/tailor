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
        'discount',
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
        // Temporary unique value, then derive the real number from the auto-increment id
        // (max(id)+1 could collide when two jobs are saved at the same time)
        static::creating(function ($job) {
            if (empty($job->job_no)) {
                $job->job_no = 'TMP-' . uniqid('', true);
            }
        });

        static::created(function ($job) {
            if (str_starts_with((string)$job->job_no, 'TMP-')) {
                $job->job_no = 'JOB-' . str_pad((string)$job->id, 6, '0', STR_PAD_LEFT);
                $job->saveQuietly();
            }
        });
    }


    public function delivery()
    {
        return $this->hasOne(\App\Models\Delivery::class, 'job_id');
    }

    /**
     * Single source of truth for the discount shown on bills/invoices:
     * the delivery discount once delivered, otherwise the job discount.
     */
    public function effectiveDiscount(): float
    {
        if ($this->delivery) {
            return (float)($this->delivery->discount ?? 0);
        }

        return (float)($this->discount ?? 0);
    }

   
}
