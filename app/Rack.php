<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class Rack extends Model
{
    protected $fillable = [
        'code',
        'code_slock',
        'name',
        'slock',
        'segment',
        'position',
        'barcode',
        'qrcode',
        'status',
        'is_active'
    ];

    public function SegmentRack()
    {
        return $this->belongsTo(SegmentRack::class, 'segment', 'code');
    }

    public function generateQrCode()
    {
        $qrCode = QrCode::format('png')->size(300)->generate($this->code);
        $fileName = 'qrcodes/rack_' . $this->code . '.png';
        Storage::disk('public')->put($fileName, $qrCode);
        $this->qrcode = $fileName;
        $this->save();
    }
}
