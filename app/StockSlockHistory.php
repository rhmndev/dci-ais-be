<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StockSlockHistory extends Model
{
    protected $fillable = [
        'slock_code',
        'rack_code',
        'job_seq',
        'material_code',
        'val_stock_value',
        'valuated_stock',
        'stock',
        'uom',
        'date_time',
        'scanned_by',
        'status',
        'date_time',
        'date_income',
        'time_income',
        'inventory_no',
        'last_time_take_in',
        'last_time_take_out',
        'take_location',
        'user_id',
        'tag',
        'note',
        'created_by',
        'updated_by',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function RackDetails()
    {
        return $this->belongsTo(Rack::class, 'rack_code', 'code');
    }

    public function UserCreateBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }

    public function UserUpdateBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'npk');
    }

    public function UserActionBy()
    {
        return $this->belongsTo(User::class, 'user_id', 'npk');
    }
}
