<?php

namespace App\Imports;

use App\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class SuppliersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
     * @param Collection $collection
     */

    public function model(array $row)
    {
        return new Supplier([
            'name' => $row[0],
            'address' => $row[1],
            'phone' => $row[2],
            'email' => $row[3],
            'contact' => $row[4],
            'currency' => $row[5]
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
