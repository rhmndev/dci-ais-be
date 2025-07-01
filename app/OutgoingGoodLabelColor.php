<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodLabelColor extends Model
{
    protected $fillable = [
        'type', 
        'month',
        'color',
    ];
}
