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
        'created_by',
        'updated_by',
        'out_by',
    ];

    public static function generateNewQRCode($jobSeq)
    {
        $qrCode = QrCode::format('png')->size(100)->generate($jobSeq);
        $qrCodePath = 'qrcodes/' . $jobSeq . '.png';
        Storage::disk('public')->put($qrCodePath, $qrCode);

        return $qrCodePath;
    }
}
