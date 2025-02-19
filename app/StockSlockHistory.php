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
        'uom',
        'date_time',
        'scanned_by',
        'status',
        'date_time',
    ];
}
