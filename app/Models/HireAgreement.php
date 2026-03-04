<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireAgreement extends Model
{
    protected $fillable = [
        'agreement_no',
        'customer_id',
        'issue_date',
        'expected_return_date',
        'actual_return_date',
        'fine_per_day',
        'fine_amount',
        'deposit_received',
        'total_hire_amount',
        'status',
        'notes',
        'created_by',
        'returned_by',
        'deposit_received',
        'amount_paid',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'fine_per_day' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'deposit_received' => 'decimal:2',
        'total_hire_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(HireAgreementItem::class)->latest();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public static function nextAgreementNo(): string
    {
        $last = self::orderByDesc('id')->value('id') ?? 0;
        $next = $last + 1;
        return 'HIRE-' . str_pad((string)$next, 6, '0', STR_PAD_LEFT);
    }
}
