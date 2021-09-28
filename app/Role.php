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

    public function getUserRole($type)
    {
        $query = new Role;

        if ($type == 1){

            $query = $query->where('name', 'Vendor');

        } else {

            $query = $query->where('name', 'Admin');

        }

        return $query->first();
    }
}
