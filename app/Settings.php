<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Settings extends Model
{
    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function scopeGetValue($query, $config)
    {
        $data = $query->where('variable', $config)->first();
        return $data->value;

    }
}
