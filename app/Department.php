<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'code',
        'name',
        'alias',
        'created_by',
        'updated_by',
    ];
}
