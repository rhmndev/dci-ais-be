<?php

use App\StockSlock;
use Illuminate\Database\Seeder;

class StockSlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'slock_code' => 'RAW1',
                'rack_code' => '1601-RW-A.MT01.1.1.1',
                'material_code' => '01A100SUS2RAW',
                'val_stock_value' => 116942570,
                'valuated_stock' => 1468.64,
                'uom' => 'KG',
            ],
            [
                'slock_code' => 'RAW1',
                'rack_code' => '1601-RW-A.MT01.1.1.3',
                'material_code' => '01A100SWH3RAW',
                'val_stock_value' => 24049020,
                'valuated_stock' => 475.7,
                'uom' => 'KG',
            ],
            [
                'slock_code' => 'RAW1',
                'rack_code' => '1601-RW-A.MT01.1.2.1',
                'material_code' => '01A120SUS1RAW',
                'val_stock_value' => 103476394,
                'valuated_stock' => 743.61,
                'uom' => 'KG',
            ],
        ];

        foreach ($data as $item) {
            StockSlock::create($item);
        }
    }
}
