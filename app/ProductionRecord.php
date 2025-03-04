<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ProductionRecord extends Model
{
    protected $fillable = [
        'user_id',
    ];
}
