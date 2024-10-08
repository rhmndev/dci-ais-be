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
        $datas = [
            ['name' => '1'],
            ['name' => '2'],
            ['name' => '3'],
            ['name' => '4'],
            ['name' => '5'],
            ['name' => '6'],
            ['name' => '7'],
            ['name' => '8'],
            ['name' => '9'],
            ['name' => '10']
        ];

        foreach ($datas as $data) {
            LineNumber::create($data);
        }
    }
}
