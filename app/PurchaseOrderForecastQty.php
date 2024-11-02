<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderForecastQty extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'forecast_qty_id',
        'qty',
    ];
}
