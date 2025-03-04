<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class CustomerScheduleDeliveryPickupTime extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_plant',
        'pickup_time',
        'type', //Planning, On Time, Delay
        'part_type',
    ];
}
