<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StockSlock extends Model
{
    protected $fillable = [
        'job_seq',
        'slock_code',
        'rack_code',
        'material_code',
        'val_stock_value',
        'valuated_stock',
        'uom',
        'date_income',
        'time_income',
        'inventory_no',
        'last_time_take_in',
        'last_time_take_out',
        'user_id',
        'tag',
        'note',
        'is_success',
        'last_changed_by',
        'last_changed_at',
        'qrcode',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function RackDetails()
    {
        return $this->hasOne(Rack::class, 'rack_code', 'code');
    }

    public function WhsMatControl()
    {
        return $this->hasOne(WhsMaterialControl::class, 'job_seq', 'job_seq');
    }

    public static function generateNewQRCode($jobSeq)
    {
        $qrCode = QrCode::format('png')->size(100)->generate($jobSeq);
        $qrCodePath = 'qrcodes/whs/stocksloc/' . $jobSeq . '.png';
        Storage::disk('public')->put($qrCodePath, $qrCode);

        return $qrCodePath;
    }
}
