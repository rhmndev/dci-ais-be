<?php

use Illuminate\Database\Seeder;
use App\Scale;

class ScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Scale::truncate();
        $datas = [
            [
                'qty' => 2000,
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ]
        ];

        foreach ($datas as $data) {

            $scale = Scale::firstOrNew(['qty' => $data['qty']]);
            $scale->qty = $data['qty'];
            $scale->created_by = $data['created_by'];
            $scale->updated_by = $data['updated_by'];
            $scale->save();

        }
    }
}
