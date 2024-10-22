<?php

namespace App;

use Jenssegers\Mongodb\Auth\User as Authenticable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticable
{
    use HasRoles;

    protected $hidden = [
        'password',
        'api_token'
    ];

    protected $fillable = ['username'];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function getList($keyword)
    {
        $query = User::query();

        if ($keyword != '') {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        $data = $query->take(10)->get();

        return $data;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
