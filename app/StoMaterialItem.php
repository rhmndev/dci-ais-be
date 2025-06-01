<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StoMaterialItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sto_material_items';

    protected $fillable = [
        'sto_material_code',
        'material_code',
        'material_name',
        'quantity_system',
        'uom_qty_system',
        'quantity_actual',
        'uom_qty_actual',
        'quantity_difference',
        'uom_qty_difference',
        'created_at',
        'updated_at',
        'completed_at',
        'completed_by',
    ];
}
