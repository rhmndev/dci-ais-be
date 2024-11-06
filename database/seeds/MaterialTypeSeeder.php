<?php

use App\MaterialType;
use Illuminate\Database\Seeder;

class MaterialTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MaterialType::truncate();

        $datas = [
            [
                'name' => "ZFIN",
                'pack_qty' => 100,
                'is_active' => true,
            ],
            [
                'name' => "ZRAW",
                'pack_qty' => 200,
                'is_active' => true,
            ],
            [
                'name' => "ZSEM",
                'pack_qty' => 500,
                'is_active' => true,
            ],
            [
                'name' => "ZOHP",
                'pack_qty' => 1000,
                'is_active' => true,
            ],
            [
                'name' => "ZGSS",
                'pack_qty' => 100,
                'is_active' => true,
            ],
            [
                'name' => "ZPCK",
                'pack_qty' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            foreach ($datas as $data) {
                MaterialType::create($data);
            }
        }
    }
}
