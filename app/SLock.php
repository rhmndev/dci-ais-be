<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SLock extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 's_locks_code', 'code');
    }
}
