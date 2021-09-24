<?php

namespace App\Imports;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\Importable;

class UsersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas, WithColumnLimit
{
    use Importable;

    public function model(array $row)
    {
        return new Delivery([
            'username'  => $row[0],
            'full_name' => $row[1],
            'department' => $row[2],
            'phone_number' => $row[3],
            'npk' => $row[4],
            'email' => $row[5],
            'type' => $row[6],
            'vendor_code' => $row[7],
        ]);
    }
    
    public function headingRow(): int
    {
        return 1;
    }

    public function endColumn(): string
    {
        return 'H';
    }
}
