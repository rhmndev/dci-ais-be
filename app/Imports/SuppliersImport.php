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
        $emails = array_map('trim', explode(',', $row['emails']));

        return new Supplier([
            'code' => $row[0],
            'name' => $row[1],
            'address' => $row[2],
            'phone' => $row[3],
            'emails' => $emails,
            'contact' => $row[5],
            'currency' => $row[6]
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
