<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerPartList extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_plant',
        'customer_alias',
        'customer_image',
        'part_no',
        'part_name',
        'part_type',
        'show',
    ];
}
