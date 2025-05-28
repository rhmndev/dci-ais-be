<?php

namespace App\Imports;

use App\Material;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class MaterialsImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    /**
    * @param Collection $collection
    */

    public function model(array $row)
    {
        return new Material([
            'code'  => $row[0],
            'description' => $row[1],
            'type' => $row[2],
            'unit' => $row[3],
            'minqty' => $row[4],
            'maxqty' => $row[5],
        ]);
    }
    
    public function headingRow(): int
    {
        return 1;
    }
}
