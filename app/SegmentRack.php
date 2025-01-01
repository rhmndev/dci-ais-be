<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SegmentRack extends Model
{
    protected $fillable = [
        'code',
        'slock',
    ];
}
