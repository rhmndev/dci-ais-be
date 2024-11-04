<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;


class Qr extends Model
{
    protected $table = 'qr';

    protected $fillable = [
        'uuid',
        'path',
        'type',
        'has_expired',
        'expired_date',
        'description',
        'created_by',
        'updated_by',
    ];

    public static function GenerateQR($type, $data)
    {
        try {
            if (!auth()->check()) {
                return false;
            }

            $renderer = new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            );

            $writer = new Writer($renderer);

            $qrCodeId = str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
            $qrImage = $writer->writeString($data);
            $fileName =  time() . '.png';
            Storage::disk('public')->put('qrcode/' . $fileName, $qrImage);

            $QrCode = new Qr;
            $QrCode->uuid = $qrCodeId;
            $QrCode->path = 'qrcode/' . $fileName;
            $QrCode->type = $type;
            $QrCode->created_by = auth()->user()->npk;
            $QrCode->save();

            return $QrCode;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'qr_uuid', 'uuid');
    }
}
