<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'staff_code',
        'phone',
        'nic',
        'address',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($profile) {
            $lastId = StaffProfile::max('id');
            $next = ($lastId ?? 0) + 1;
            $profile->staff_code = 'STF-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        });
    }
}