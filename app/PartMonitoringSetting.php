<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartMonitoringSetting extends Model
{
    protected $fillable = [
        'enable_whatsapp',
        'whatsapp_numbers'
    ];
}
