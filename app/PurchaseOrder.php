<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'order_date',
        'delivery_date',
        'supplier_id',
        'total_item_quantity',
        'total_amount',
        'status',
        'created_by',
        'updated_by',
    ];
}
