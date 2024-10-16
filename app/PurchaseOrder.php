<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\PurchaseOrderActivities;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'user',
        'order_date',
        'delivery_email',
        'delivery_date',
        'delivery_address',
        'supplier_id',
        'supplier_code',
        'total_item_quantity',
        'total_amount',
        'purchase_checked_by',
        'checked_at',
        'purchase_knowed_by',
        'knowed_at',
        'purchase_agreement_by',
        'approved_at',
        'status',
        'created_by',
        'updated_by',
    ];

    // Define the relationship to Supplier
    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'code', 'supplier_code');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }


    public function purchaseOrderActivity()
    {
        return $this->hasOne(PurchaseOrderActivities::class, 'po_number', 'po_number');
    }
}
