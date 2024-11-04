<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderAnalytics extends Model
{
    protected $fillable = [
        'month_year',
        'total_orders_all',
        'total_pending_all',
        'total_approved_all',
        'total_unapproved_all',
        'total_delivered_all',
        'total_orders',
        'total_pending',
        'total_approved',
        'total_unapproved',
        'total_delivered',
    ];
}
