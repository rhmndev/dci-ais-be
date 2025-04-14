<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MpOvertimeLog extends Model
{
    protected $fillable = [
        'overtime_id',
        'dept_code',
        'shift_code',
        'total_mp',
        'place_code',
        'created_by',
        'updated_by',
    ];

    public function overtime()
    {
        return $this->belongsTo(MpOvertime::class, 'overtime_id');
    }
}
