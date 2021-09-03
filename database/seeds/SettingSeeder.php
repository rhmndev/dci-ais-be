<?php

use Illuminate\Database\Seeder;
use App\Settings;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Settings::truncate();
        
        $datas = [
            [
                'variable' => 'code_sap',
                'value' => json_decode('[
                    {"id":0.10368457669327125,"name":"1600"},
                    {"id":0.9861566506917219,"name":"1601"}
                ]'),
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ],
            [
                'variable' => 'POStatus',
                'value' => json_decode('[
                    {"id":0.39275581724538133,"name":"Complete"},
                    {"id":0.13603751390670915,"name":"In Progress"}
                ]'),
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ],
            [
                'variable' => 'PPN',
                'value' => json_decode('[
                    {"id":0.8180006182929263,"name":"V1;10%"}
                ]'),
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ],
            [
                'variable' => 'Material_Perpage',
                'value' => json_decode('[
                    {"id":0.8293378662731039,"name":"20"}
                ]'),
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {

            $settings = Settings::firstOrNew(['variable' => $data['variable']]);
            $settings->variable = $data['variable'];
            $settings->value = $data['value'];
            $settings->created_by = $data['created_by'];
            $settings->changed_by = $data['changed_by'];
            $settings->save();

        }
    }
}
