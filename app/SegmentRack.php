<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SegmentRack extends Model
{
    protected $fillable = [
        'plant',
        'code',
        'name',
        'slock',
    ];

    public function racks()
    {
        return $this->hasMany(Rack::class, 'segment', 'code');
    }
}
