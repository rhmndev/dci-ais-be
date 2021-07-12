<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Permission extends Model
{
    protected $dates = [
        'deleted_at'
    ];

    protected $fillable = [
        'permission_id', 
        'allow'
    ];

    public function children()
    {
        return $this->hasMany('App\Permission', 'parent_id', '_id')->orderBy('order_number');
    }
}
