<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class UserSupplier extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'user_suppliers';

    protected $fillable = [
        'username',
        'password',
        'email',
        'full_name',
        'type',
        'role_id',
        'role_name',
        'vendor_code',
        'vendor_name',
        'created_at',
        'updated_at',
    ];
}
