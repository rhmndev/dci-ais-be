<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SubContMaterial extends Model
{
    protected $fillable = [
        'subcont_code',
        'material_code',
        'material_name',
        'created_by',
        'updated_by',
    ];

    public function subcont()
    {
        return $this->belongsTo(SubCont::class, 'subcont_code', 'code');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'npk');
    }
}
