<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $table = 'shipping_addresses';
    protected $fillable = ['full_address'];
}
