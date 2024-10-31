<?php

use App\ForecastQty;
use Illuminate\Database\Seeder;

class ForecastQtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ForecastQty::truncate();
        $datas = [
            ['name' => 'n + 1', 'is_active' => true],
            ['name' => 'n + 2', 'is_active' => true],
            ['name' => 'n + 3', 'is_active' => true],
        ];

        foreach ($datas as $data) {
            ForecastQty::create($data);
        }
    }
}
