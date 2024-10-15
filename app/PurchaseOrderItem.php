<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'material_id',
        'quantity',
        'unit_type',
        'unit_price',
        'unit_price_type',
    ];
}
