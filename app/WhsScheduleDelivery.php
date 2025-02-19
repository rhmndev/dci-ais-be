<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class WhsScheduleDelivery extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_plant',
        'part_type',
        'schedule_date',
        'part_id',
        'part_number',
        'part_name',
        'cycle',
        'qty',
        'planning_time',
        'on_time',
        'delay',
        'status_prod',
        'status_qc',
        'status_spa',
        'status_ok',
        'status_ready_to_delivery',
        'status_delivery',

        'po_no',
        'ext_delivery_id',
        'delivery',
        'customer_mat_no',
        'description',
        'del_qty',
        'net_price',
        'total',
        'pod_status',
        'gm',
        'bs',
        'currency',
        'su',
        'material',
        'shpt',
        'ac_gi_date',

        'created_by',
    ];
}
