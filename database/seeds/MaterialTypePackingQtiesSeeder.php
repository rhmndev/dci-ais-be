<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialTypePackingQtiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('material_type_packing_qties')->truncate();
        $faker = Faker\Factory::create();

        $types = ['ZFIN', 'ZRAW', 'ZSEM', 'ZOHP', 'ZGSS', 'ZPCK'];

        foreach ($types as $type) {
            DB::table('material_type_packing_qties')->insert([
                'material_type' => $type,
                'pack_qty' => $faker->randomElement([100, 200, 500, 1000]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
