<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MpOvertimeSetting extends Model
{
    protected $fillable = [
        'start_time_open_overtime',
        'end_time_open_overtime',
        'enable_whatsapp',
        'whatsapp_numbers'
    ];
}
