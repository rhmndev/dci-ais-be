<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WhsMaterialControl extends Model
{
    protected $fillable = [
        'material_code',
        'stock',
        'uom',
        'seq_no',
        'job_seq',
        'qr_code',
        'tag',
        'loc_in',
        'in_at',
        'loc_out_to',
        'last_loc_out_to',
        'last_out_at',
        'stock_out',
        'status',
        'note',
        'is_out',
        'created_by',
        'updated_by',
        'out_by',
        'out_note',
    ];

    const STATUS_IN = 'IN';
    const STATUS_OUT = 'OUT';

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_code', 'code');
    }

    public function StockSlockDetails()
    {
        return $this->hasOne(StockSlock::class, 'job_seq', 'job_seq');
    }

    public function UserCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'npk');
    }
    public function UserUpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'npk');
    }
    public function UserOutBy()
    {
        return $this->belongsTo(User::class, 'out_by', 'npk');
    }

    public static function generateNewQRCode($jobSeq)
    {
        $qrCode = QrCode::format('png')->size(100)->generate($jobSeq);
        $qrCodePath = 'qrcodes/whs/' . $jobSeq . '.png';
        Storage::disk('public')->put($qrCodePath, $qrCode);
        return $qrCodePath;
    }
}
