<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MpOvertimeSetting extends Model
{
    protected $fillable = [
        'enable_whatsapp',
        'whatsapp_numbers'
    ];
}
