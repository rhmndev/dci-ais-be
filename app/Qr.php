<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;


class Qr extends Model
{
    protected $table = 'qr';

    protected $fillable = [
        'uuid',
        'path',
        'type',
        'has_expired',
        'expired_date',
        'description',
        'created_by',
        'updated_by',
    ];
}
