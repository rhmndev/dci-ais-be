<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StorageSlocAreaRack extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'storage_sloc_area_code',
        'code',
        'name',
        'position',
    ];
}
