<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StorageSlocArea extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'plant',
        'sloc',
        'code',
        'name',
        'alias',
        'created_at',
        'updated_at',
    ];
}
