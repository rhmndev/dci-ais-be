<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class LineNumber extends Model
{
    protected $table = 'line_number';
    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];
}
