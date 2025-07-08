<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StockSlocTakeOutTemp extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'job_seq',
        'material_code',
        'sloc_code',
        'rack_code',
        'pkg_no',
        'uom',
        'qty',
        'uom_take_out',
        'qty_take_out',
        'user_id',
        'status',
        'note',
        'is_success',
    ];

    public function StockSlock()
    {
        return $this->belongsTo(StockSlock::class, 'job_seq', 'job_seq');
    }
}
