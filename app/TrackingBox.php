<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TrackingBox extends Model
{
    protected $fillable = [
        'number_box',
        'dn_number',
        'kanban',
        'customer',
        'plant',
        'destination_code',
        'destination_aliases',
        'status',
        'date_time',
        'scanned_by',
    ];

    public function Box()
    {
        return $this->belongsTo(Box::class, 'number_box', 'number_box');
    }

    public function getTrackingBoxes()
    {
        return TrackingBox::where('dn_number', $this->dn_number)->get();
    }
}
