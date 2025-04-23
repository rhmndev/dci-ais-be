<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class WhsScheduleDeliveryLog extends Model
{
    protected $fillable = [
        'whs_schedule_delivery_id',
        'action',
        'changes',
        'performed_by',
    ];

    public function whsScheduleDelivery()
    {
        return $this->belongsTo(WhsScheduleDelivery::class, 'whs_schedule_delivery_id');
    }
}
