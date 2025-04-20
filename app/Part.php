<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Part extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category_code',
        'category_name',
        'uom',
        'min_stock',
        'max_stock',
        'rack',
        'brand_code',
        'brand_name',
        'qr_code',
        'is_partially_out',
        'is_out_target',
        'created_by',
        'last_updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(PartCategories::class, 'category_code', 'code');
    }

    public function partStock()
    {
        return $this->hasOne(PartStock::class, 'part_code', 'code');
    }

    public static function generateNewCode()
    {
        $lastPart = self::orderBy('created_at', 'desc')->first();
        if ($lastPart) {
            $lastCode = $lastPart->code;
            $lastNumber = (int) substr($lastCode, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        return 'PART' . $newNumber;
    }

    public function generateQRCode()
    {
        $this->qr_code = 'qrcodes/part_' . $this->code . '.png';
        $qrCode = QrCode::format('png')->size(200)->generate($this->code);
        Storage::disk('public')->put($this->qr_code, $qrCode);
        $this->save();
    }
}
