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
        'qrcode',
        'status',
        'is_active'
    ];

    public function SegmentRack()
    {
        return $this->belongsTo(SegmentRack::class, 'segment', 'code');
    }
}
