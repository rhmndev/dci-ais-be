<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SupplierPart extends Model
{
    protected $table = 'supplier_part';

    protected $fillable = [
        'supplier_id',
        'part_id',
        'part_number',
        'unit_id',
    ];

    public function supplier()
    {
        return $this->hasOne(Supplier::class, '_id', 'supplier_id');
    }

    public function part()
    {
        return $this->hasOne(Material::class, '_id', 'part_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
