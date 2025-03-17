<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartStock extends Model
{
    protected $fillable = [
        'part_code',
        'stock',
        'created_by',
        'updated_by',
    ];

    public function UserCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }

    public function UserUpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'npk');
    }

    public static function updateIncreaseStock($partCode, $stock, $user)
    {
        $partStock = PartStock::where('part_code', $partCode)->first();
        if ($partStock) {
            $partStock->stock += $stock;
            $partStock->updated_by = $user->npk;
            $partStock->save();
        } else {
            PartStock::create([
                'part_code' => $partCode,
                'stock' => $stock,
                'created_by' => $user->npk,
                'updated_by' => $user->npk,
            ]);
        }
    }

    public static function updateReduceStock($partCode, $stock, $user)
    {
        $partStock = PartStock::where('part_code', $partCode)->first();
        if ($partStock) {
            $partStock->stock -= $stock;
            $partStock->updated_by = $user->npk;
            $partStock->save();
        } else {
            PartStock::create([
                'part_code' => $partCode,
                'stock' => -$stock,
                'created_by' => $user->npk,
                'updated_by' => $user->npk,
            ]);
        }
    }
}
