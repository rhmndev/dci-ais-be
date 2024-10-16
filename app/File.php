<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['user_id', 'name', 'path', 'size', 'type', 'ext', 'expires_at', 'is_expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
