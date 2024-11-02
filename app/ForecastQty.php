<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ForecastQty extends Model
{
    protected $fillable = [
        'name',
        'order',
        'is_active'
    ];
}
