<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PoTrackingEvent extends Model
{
    protected $fillable = ['po_id', 'event', 'occurred_at', 'notes'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
}
