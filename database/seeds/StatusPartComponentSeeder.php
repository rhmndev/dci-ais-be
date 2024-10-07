<?php

use App\StatusPartComponent;
use Illuminate\Database\Seeder;

class StatusPartComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'type' => 'rejected',
                'group_type' => '2W',
                'name' => 'Bushing Cacat',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'type' => 'rejected',
                'group_type' => '2W',
                'name' => 'Bushing Pecah',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'type' => 'rejected',
                'group_type' => '2W',
                'name' => 'Bolt Cacat',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {
            StatusPartComponent::create($data);
        }
    }
}
