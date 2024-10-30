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

        if ($type == 1) {

            $query = $query->where('name', 'Vendor');
        } else {
            if ($type == 0) {
                $query = $query->where('name', 'Admin');
            } else {
                $query = $query->where('name', 'Purchasing');
            }
        }

        return $query->first();
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department_id');
    }
}
