<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MaterialTypePackingQties extends Model
{
    protected $fillable = [
        'material_type',
        'pack_qty',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'type', 'material_type');
    }
}
