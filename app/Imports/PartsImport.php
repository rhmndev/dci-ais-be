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
        // if isset  $row['can_parsially_out'] then have value is Y will be true
        // if isset  $row['must_select_out_target'] then have value is Y will be true

        $row['can_parsially_out'] = isset($row['can_parsially_out']) && $row['can_parsially_out'] == 'Y' ? true : false;
        $row['must_select_out_target'] = isset($row['must_select_out_target']) && $row['must_select_out_target'] == 'Y' ? true : false;

        return new Part([
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'],
            // 'category_code' => $row['category_code'],
            // 'category_name' => $row['category_name'],
            'uom' => $row['uom'],
            'min_stock' => $row['min_stock'] ?? 0,
            'max_stock' => $row['max_stock'] ?? 0,
            'rack' => $row['rack'],
            'stock' => $row['stock'] ?? 0,
            'brand_name' => $row['brand_name'],
            'is_partially_out' => $row['can_parsially_out'] ?? false,
            'is_out_target' => $row['must_select_out_target'] ?? false,
            // 'qr_code' => Part::generateQRCode(),
            'qr_code' => '',
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
