<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SubCont extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'npk');
    }
}
