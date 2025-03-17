<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PartControl extends Model
{
    protected $fillable = [
        'part_code',
        'seq_no',
        'job_seq',
        'qr_code',
        'in_at',
        'out_at',
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

    public function UserOutBy()
    {
        return $this->belongsTo(User::class, 'out_by', 'npk');
    }

    public static function generateNewQRCode($jobSeq)
    {
        $qrCode = QrCode::format('png')->size(100)->generate($jobSeq);
        $qrCodePath = 'qrcodes/' . $jobSeq . '.png';
        Storage::disk('public')->put($qrCodePath, $qrCode);

        return $qrCodePath;
    }
}
