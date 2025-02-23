<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StockSlock extends Model
{
    protected $fillable = [
        'slock_code',
        'rack_code',
        'material_code',
        'val_stock_value',
        'valuated_stock',
        'uom',
        'time_income',
        'last_time_take_in',
        'last_time_take_out',
        'user_id',
    ];
}
