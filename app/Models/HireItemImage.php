<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireItemImage extends Model
{
    protected $fillable = ['hire_item_id', 'image_path'];

    public function item()
    {
        return $this->belongsTo(HireItem::class, 'hire_item_id');
    }
}