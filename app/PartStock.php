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

    public function part()
    {
        return $this->belongsTo(Part::class, 'part_code', 'code');
    }

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
        $newStock = 0;

        if ($partStock) {
            $partStock->stock += $stock;
            $partStock->updated_by = $user->npk;
            $partStock->save();
            $newStock = $partStock->stock;
        } else {
            $partStock = PartStock::create([
                'part_code' => $partCode,
                'stock' => $stock,
                'created_by' => $user->npk,
                'updated_by' => $user->npk,
            ]);
            $newStock = $stock;
        }

        PartStockLog::create([
            'part_code' => $partCode,
            'stock_change' => $stock,
            'new_stock' => $newStock,
            'action' => 'increase',
            'created_by' => $user->npk,
        ]);
    }

    public static function updateReduceStock($partCode, $stock, $user, $out_to = null)
    {
        $partStock = PartStock::where('part_code', $partCode)->first();
        $newStock = 0;
        if ($partStock) {
            $partStock->stock -= $stock;
            $partStock->updated_by = $user->npk;
            $partStock->save();
            $newStock = $partStock->stock;
        } else {
            $partStock = PartStock::create([
                'part_code' => $partCode,
                'stock' => -$stock,
                'created_by' => $user->npk,
                'updated_by' => $user->npk,
            ]);
            $newStock = -$stock;
        }

        PartStockLog::create([
            'part_code' => $partCode,
            'stock_change' => -$stock,
            'new_stock' => $newStock,
            'action' => 'decrease',
            'out_to' => $out_to,
            'created_by' => $user->npk,
        ]);
    }
}
