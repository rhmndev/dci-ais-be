<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class RackMaterialPosition extends Model
{
    protected $fillable = [
        'code_rack',
        'code_material',
        'stok',
        'in_at',
        'out_at',
        'status'
    ];
}
