<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusPartComponent extends Model
{
    use SoftDeletes;
    protected $table = 'status_part_component';
    protected $fillable = [
        'type',
        'group_type',
        'name'
    ];
}
