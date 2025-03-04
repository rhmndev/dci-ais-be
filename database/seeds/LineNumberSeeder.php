<?php

use App\LineNumber;
use Illuminate\Database\Seeder;

class LineNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        LineNumber::truncate();
        $lineNumbers = [
            [
                'code' => 'LINE001',
                'name' => 'Line Number 1',
                'qr_path' => 'path/to/qr_code1.png',
                'is_active' => true,
            ],
            [
                'code' => 'LINE002',
                'name' => 'Line Number 2',
                'qr_path' => 'path/to/qr_code2.png',
                'is_active' => true,
            ],
            [
                'code' => 'LINE003',
                'name' => 'Line Number 3',
                'qr_path' => 'path/to/qr_code3.png',
                'is_active' => true,
            ],
        ];

        foreach ($lineNumbers as $lineNumber) {
            LineNumber::create($lineNumber);
        }
    }
}
