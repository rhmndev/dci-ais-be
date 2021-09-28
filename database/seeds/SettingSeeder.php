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
            [
                'variable' => 'Gudang',
                'value' => json_decode('[
                    {"id":0.9774112388693914,"name":"CH01;CHEMICAL"}
                    {"id":0.7169090400554472,"name":"CO01;CONSUMABLE"}
                    {"id":0.6666342468858273,"name":"CS01;CUSTOMER SUPPLY"}
                    {"id":0.6246999578574441,"name":"DI01;DISCONTINUE"}
                    {"id":0.6584882123637634,"name":"EN01;ENGINEERING"}
                    {"id":0.1769217311649797,"name":"GS01;GS / ATK"}
                    {"id":0.1204737882898632,"name":"MT01;MAINTENANCE"}
                    {"id":0.14935756956400503,"name":"OH01;OHP (OUT HOUSE P"}
                    {"id":0.07490602284114334,"name":"PB01;POOLING BUYING M"}
                    {"id":0.97406668456854,"name":"PB02;POOLING BUYING F"}
                    {"id":0.5598219072063504,"name":"PE01;PROD. ENGINEERIN"}
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
