<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'code',
        'name',
        'alias',
        'start_time',
        'end_time',
        'created_by',
        'updated_by',
    ];
}
