<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PurchaseOrderSigner extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'npk',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
