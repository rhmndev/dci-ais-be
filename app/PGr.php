<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PGr extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];
}
