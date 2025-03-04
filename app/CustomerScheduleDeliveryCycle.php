<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class CustomerScheduleDeliveryCycle extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_plant',
        'customer_alias',
        'cycle',
    ];
}
