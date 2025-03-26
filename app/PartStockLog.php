<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartStockLog extends Model
{
    protected $fillable = [
        'part_code',
        'ref_job_seq',
        'stock_change',
        'new_stock',
        'action',
        'out_to',
        'created_by',
    ];


    public function PartControl()
    {
        return $this->belongsTo(PartControl::class, 'ref_job_seq', 'job_seq');
    }

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_code', 'code');
    }

    public function UserCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }
}
