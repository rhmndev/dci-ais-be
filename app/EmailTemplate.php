<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'template_name',
        'template_type',
        'cc',
        'bcc',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array', // Cast variables to an array
    ];
}
