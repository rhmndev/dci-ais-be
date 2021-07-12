<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Role extends Model
{
    protected $dates = [
        'deleted_at'
    ];

    public function permissions()
    {
        return $this->embedsMany(Permission::class);
    }
}
