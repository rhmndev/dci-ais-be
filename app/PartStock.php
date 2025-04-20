<?php

namespace App;

use App\Helpers\WhatsappHelper;
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

    public function PartStockLogs()
    {
        return $this->hasMany(PartStockLog::class, 'part_code', 'part_code');
    }

    public static function updateIncreaseStock($partCode, $stock, $user)
    {
        $partStock = PartStock::with('part')->where('part_code', $partCode)->first();
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

        self::checkStockStatusAndNotify($partStock);
    }

    public static function updateReduceStock($partCode, $stock, $user, $out_to = null)
    {
        $partStock = PartStock::with('part')->where('part_code', $partCode)->first();
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

        self::checkStockStatusAndNotify($partStock);
    }

    protected static function checkStockStatusAndNotify($partStock)
    {
        $setting = PartMonitoringSetting::first();
        if (!$setting || !$setting->enable_whatsapp || !$setting->whatsapp_numbers) return;

        $part = $partStock->part; // pastikan relasi `part()` didefinisikan di model PartStock
        $stock = $partStock->stock;

        $stock = (int) $stock;
        $minStock = is_numeric($part->min_stock) ? (int) $part->min_stock : null;
        $maxStock = is_numeric($part->max_stock) ? (int) $part->max_stock : null;
        $message = null;

        if ($stock <= 0) {
            $message = "⚠️ Part {$part->code} - {$part->name} is *Out of Stock*! Current stock: {$stock}.";
        } elseif (!is_null($minStock) && $stock < $minStock) {
            $message = "🔻 Part {$part->code} - {$part->name} is *Low Stock*! Current stock: {$stock}. Min required: {$minStock}.";
        } elseif (!is_null($maxStock) && $stock > $maxStock) {
            $message = "🔺 Part {$part->code} - {$part->name} is *Over Stock*! Current stock: {$stock}. Max allowed: {$maxStock}.";
        }

        if ($message) {
            $numbers = explode(',', $setting->whatsapp_numbers);
            WhatsappHelper::sendMessage($numbers, $message);
        }
    }
}
