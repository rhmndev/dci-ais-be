<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StorageSlocArea extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'plant',
        'slock',
        'code',
        'name',
        'index',
        'alias',
        'type',
        'position',
        'created_at',
        'updated_at',
    ];

    public function Racks()
    {
        return $this->hasMany(StorageSlocAreaRack::class,'storage_sloc_area_code','code');
    }
}
