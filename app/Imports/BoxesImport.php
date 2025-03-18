<?php

namespace App\Imports;

use App\Box;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoxesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Box([
            'number_box' => $row['number_box'],
            'type_box' => $row['type_box'],
            'status_box' => $row['status_box'],
            'plant' => '1601',
            'qr_code' => $this->generateQrCode('1601-' . $row['number_box'] . '-' . $row['type_box']),
        ]);
    }

    private function generateQrCode($number_box)
    {
        $qrCode = QrCode::format('png')->size(200)->generate($number_box);
        $fileName = 'qrcodes/' . $number_box . '.png';
        Storage::put('public/' . $fileName, $qrCode);
        return $fileName;
    }
}
