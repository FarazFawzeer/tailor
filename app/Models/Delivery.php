<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'job_id',
        'delivered_date',
        'delivered_by',
        'sub_total',
        'discount',
        'grand_total',
        'notes',
    ];

    protected $casts = [
        'delivered_date' => 'date',
    ];

    public function job()
    {
        return $this->belongsTo(\App\Models\Job::class);
    }

    public function deliveredByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'delivered_by');
    }
}