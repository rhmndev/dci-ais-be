<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'po_number',
        'material_id',
        'material_code',
        'quantity',
        'unit_type',
        'unit_price',
        'unit_price_type',
        'unit_price_amount',
        'net_price',
        'qty_delivered',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'partially_delivered_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', '_id');
    }

    public function material()
    {
        return $this->hasOne(Material::class, '_id', 'material_id');
    }

    public function travelDocumentItem()
    {
        return $this->hasMany(TravelDocumentItem::class, 'po_item_id', '_id');
    }

    public function travelDocumentItemLabelTemp()
    {
        return $this->hasMany(TravelDocumentLabelTemp::class, 'po_item_id', '_id');
    }
}
