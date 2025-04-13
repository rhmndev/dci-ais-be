<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MpOvertime extends Model
{
    protected $fillable = [
        'dept_code',
        'date',
        'shift_code',
        'total_mp',
        'place_code',
        'created_by',
        'updated_by',
    ];

    public function logs()
    {
        return $this->hasMany(MpOvertimeLog::class, 'overtime_id');
    }
}
