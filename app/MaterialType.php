<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MaterialType extends Model
{
    protected $fillable = [
        'name',
        'pack_qty',
        'is_active',
    ];

    public function material()
    {
        return $this->hasMany(Material::class, 'type', 'name');
    }
}
