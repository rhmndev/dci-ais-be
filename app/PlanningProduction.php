<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PlanningProduction extends Model
{
    protected $fillable = [
        'code',
        'name',
        'part_code',
        'part_description',
        'target_quantity',
        'total_hours',
        'qr_code',
        'status',
        'is_active'
    ];

    public static function generateNewCode()
    {
        $lastPlanning = self::orderBy('created_at', 'desc')->first();
        if ($lastPlanning) {
            $lastCode = $lastPlanning->code;
            $lastNumber = (int) substr($lastCode, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        return 'PLN' . $newNumber;
    }

    public function generateQRCode()
    {
        $this->qr_code = 'qrcodes/planning_' . $this->code . '.png';
        // Generate QR code and store in storage/app/public/qrcodes
        $qrCode = QrCode::format('png')->size(200)->generate($this->code);
        Storage::disk('public')->put($this->qr_code, $qrCode);
        $this->save();
    }
}
