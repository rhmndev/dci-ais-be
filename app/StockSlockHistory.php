<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StockSlockHistory extends Model
{
    protected $fillable = [
        'slock_code',
        'rack_code',
        'material_code',
        'val_stock_value',
        'valuated_stock',
        'stock',
        'uom',
        'date_time',
        'scanned_by',
        'status',
        'date_time',
        'date_income',
        'time_income',
        'last_time_take_in',
        'last_time_take_out',
        'take_location',
        'user_id',
        'tag',
        'note',
        'created_by',
        'updated_by',
    ];


    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }
}
