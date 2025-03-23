<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StockSlock extends Model
{
    protected $fillable = [
        'slock_code',
        'rack_code',
        'material_code',
        'val_stock_value',
        'valuated_stock',
        'uom',
        'date_income',
        'time_income',
        'last_time_take_in',
        'last_time_take_out',
        'user_id',
        'tag',
        'note',
        'is_success',
        'last_changed_by',
        'last_changed_at',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function RackDetails()
    {
        return $this->hasOne(Rack::class, 'rack_code', 'code');
    }
}
