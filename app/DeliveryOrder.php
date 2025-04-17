<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'sold_to_pt',
        'name_1',
        'doc_date',
        'purchase_order_no',
        'external_delivery_id',
        'delivery',
        'customer_material_number',
        'description',
        'delivery_quantity',
        'net_price',
        'total',
        'pod_status',
        'gm',
        'bs',
        'plant',
        'currency',
        'su',
        'material',
        'shpt',
        'ac_gi_date',
        'time',
        'pod_date',
        'po_date',
        'ref_doc',
        'sorg',
        'curr',
        'dlvt',
        'ship_to',
        'name_1_ship_to',
        'dchl',
        'item',
        'sloc',
        'mat_frt_gp',
        'gi_indicator',
        'quantity_dn',
        'su_dn',
        'status_dn',
        'dn_customer',
        'zsogdo_cgrn',
        'nomor_kendaraan',
        'pod',
        'sales_text',
    ];
}
