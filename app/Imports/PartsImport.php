<?php

namespace App\Imports;

use App\Part;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class PartsImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        return new Part([
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'],
            'category_code' => $row['category_code'],
            'category_name' => $row['category_name'],
            'uom' => $row['uom'],
            'min_stock' => $row['min_stock'],
            'qr_code' => Part::generateQRCode(),
        ]);
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
    }
}
