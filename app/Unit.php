<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'is_active'];
}
