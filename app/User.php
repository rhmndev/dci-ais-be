<?php

namespace App;

use Jenssegers\Mongodb\Auth\User as Authenticable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class User extends Authenticable
{
    use HasRoles, Notifiable, SoftDeletes;

    protected $hidden = [
        'password',
        'api_token'
    ];

    protected $fillable = ['username', 'npk', 'is_admin', 'login_attempts', 'is_locked'];

    protected $dates = ['deleted_at'];

    public function role()
    {
        return $this->belongsTo('App\Role', 'role_name', 'name');
    }

    public function getList($keyword, $type = '', $takeAll = false, $withTrashed = false)
    {
        $query = User::query();

        if ($withTrashed) {
            $query = $query->withTrashed();
        }

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
