<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompareDeliveryNoteAHM extends Model
{
    protected $connection = 'mysql';

    protected $table = 'compare_ahm';

    protected $fillable = [
        'dn_no',
        'part_no',
        'job_seq',
        'po',
        'date_scan',
        'user_id'
    ];

    public function trackingBoxes()
    {
        return TrackingBox::where('kanban', $this->job_seq)->get();
    }
}
