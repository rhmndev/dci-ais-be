<?php

namespace App;

use Jenssegers\Mongodb\Auth\User as Authenticable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

class User extends Authenticable
{
    use HasRoles, Notifiable;

    protected $hidden = [
        'password',
        'api_token'
    ];

    protected $fillable = ['username', 'npk', 'is_admin'];

    public function role()
    {
        return $this->belongsTo('App\Role', 'role_name', 'name');
    }

    public function getList($keyword, $type = '', $takeAll = false)
    {
        $query = User::query();

        if ($type != '') {
            $query = $query->where('type', $type);
        }

        if ($keyword != '') {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($takeAll) {
            return $query->get();
        }

        $data = $query->take(10)->get();

        return $data;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function partControls()
    {
        return $this->hasMany(PartControl::class, 'created_by', 'npk');
    }
}
