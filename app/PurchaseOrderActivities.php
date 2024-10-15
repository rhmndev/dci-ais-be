<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use App\PurchaseOrder;

class PurchaseOrderActivities extends Model
{
    protected $fillable = [
        'po_id',
        'po_number',
        'seen',
        'last_seen_at',
        'downloaded',
        'last_downloaded_at',
    ];

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'po_number', 'po_number');
    }
}
