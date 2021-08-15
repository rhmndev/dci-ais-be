<?php

use Illuminate\Database\Seeder;
use App\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Material::truncate();
        $datas = [
            [
                'code' => '021PCK0010013',
                'description' => 'PLASTIK P-011 AHM',
                'type' => 'ZCUS',
                'unit' => 'PCE',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'code' => '021PCK0010014',
                'description' => 'PLASTIK P-014 AHM',
                'type' => 'ZCUS',
                'unit' => 'PCE',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {

            $material = Material::firstOrNew(['code' => $data['code']]);
            $material->code = $data['code'];
            $material->description = $data['description'];
            $material->type = $data['type'];
            $material->unit = $data['unit'];
            $material->photo = null;
            $material->created_by = $data['created_by'];
            $material->updated_by = $data['updated_by'];
            $material->save();

        }
    }
}
