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
        'take_in_at',
        'take_out_at',
        'user_id',
    ];
}
