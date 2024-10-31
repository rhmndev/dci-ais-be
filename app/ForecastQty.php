<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ForecastQty extends Model
{
    protected $fillable = [
        'name',
        'is_active'
    ];
}
