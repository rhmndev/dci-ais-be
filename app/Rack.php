<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Rack extends Model
{
    protected $fillable = [
        'code',
        'slock',
        'segment',
        'position',
        'barcode',
        'status',
        'is_active'
    ];
}
