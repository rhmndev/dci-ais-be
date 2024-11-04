<?php

use App\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Unit::truncate();
        $datas = [
            [
                'name' => 'pcs',
            ],
            [
                'name' => 'kg',
            ],
            [
                'name' => 'mtr',
            ],
        ];

        foreach ($datas as $data) {
            $Unit = new Unit;
            $Unit->name = $data['name'];
            $Unit->save();
        }
    }
}
