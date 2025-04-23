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
        'status_prd',
        'status_qc',
        'status_spa',
        'status_ok',
        'status_ready_to_delivery',
        'status_delivery',

        'po_no',
        'pod_date',
        'po_date',
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
        'dlvt',
        'su',
        'ship_to',
        'name_ship_to',
        'dchl',
        'ref_doc',
        'sloc',
        'sorg',
        'material',
        'shpt',
        'ac_gi_date',
        'quantity_dn',
        'item',
        'status_dn',
        'dn_customer',
        'time',

        'created_by',
    ];

    public function CreatedUserBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }

    public function logs()
    {
        return $this->hasMany(WhsScheduleDeliveryLog::class);
    }
}
