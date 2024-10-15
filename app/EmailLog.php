<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'recipient',
        'subject',
        'message',
        'status',
        'error_message',
    ];
}
