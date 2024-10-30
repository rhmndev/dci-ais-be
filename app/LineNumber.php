<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class LineNumber extends Model
{
    protected $table = 'line_number';
    protected $fillable = [
        'name',
        'is_active'
    ];
}
