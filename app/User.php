<?php

namespace App;

use Jenssegers\Mongodb\Auth\User as Authenticable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticable
{
    public $dates = ['deleted_at'];
    
    protected $hidden = [
        'password', 
        'api_token'
    ];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
