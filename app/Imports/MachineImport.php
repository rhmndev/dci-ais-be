<?php

namespace App\Imports;

use App\Machine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class MachineImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Machine([
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'],
        ]);
    }
}
