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

        switch ($type) {
            case 0:
                $query = $query->where('name', 'Admin');
                break;
            case 1:
                $query = $query->where('name', 'Vendor');
                break;
            case 3:
                $query = $query->where('name', 'Purchasing');
                break;
            case 4:
                $query = $query->where('name', 'Warehouse');
                break;

            default:
                $query = $query->where('name', 'Supplier');
                break;
        }

        return $query->first();
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department_id');
    }
}
