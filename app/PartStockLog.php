<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartStockLog extends Model
{
    protected $fillable = [
        'part_code',
        'stock_change',
        'new_stock',
        'action',
        'out_to',
        'created_by',
    ];
}
