<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPayment extends Model
{
    public const TYPES = [
        'advance'    => 'Advance',
        'payment'    => 'Payment',
        'settlement' => 'Final Settlement',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'card' => 'Card',
        'bank' => 'Bank Transfer',
        'other' => 'Other',
    ];

    protected $fillable = [
        'job_id',
        'amount',
        'payment_type',
        'method',
        'paid_at',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->payment_type] ?? ucfirst((string)$this->payment_type);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst((string)$this->method);
    }
}
