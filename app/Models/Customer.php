<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'full_name',
        'phone',
        'email',
        'nic',
        'address',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($customer) {
            $lastId = Customer::max('id');
            $next = ($lastId ?? 0) + 1;
            $customer->customer_code = 'CUS-' . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
        });
    }
}