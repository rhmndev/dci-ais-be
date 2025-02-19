<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompareDeliveryNote extends Model
{
    protected $connection = 'mysql';

    protected $table = 'compare';

    protected $fillable = [
        'dn_no',
        'job_no',
        'kbn_no',
        'date_scan',
        'user_id'
    ];

    public function OrderCustomer()
    {
        return $this->belongsTo(OrderCustomer::class, 'dn_no', 'dn_no');
    }

    public function trackingBoxes()
    {
        return TrackingBox::where('kanban', $this->kbn_no)->get();
    }
}
