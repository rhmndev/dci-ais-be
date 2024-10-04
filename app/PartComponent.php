<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartComponent extends Model
{
    protected $fillable = ['part_components_id'];

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}
