<?php

use App\PartComponent;
use Illuminate\Database\Seeder;

class PartComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PartComponent::truncate();
        $datas = [
            [
                'customer_id' => '6702cf3d7d4ac57e2c01f53c', // Replace with actual customer ID
                'name' => 'CABLE COMP A THROTTLE K1A',
                'number' => '17910-K1A -N020-M2',
                'photo' => 'component.jpg', // Replace with actual photo filename
                'description' => 'Description for Component',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'customer_id' => '6702cf3d7d4ac57e2c01f53c', // Replace with actual customer ID
                'name' => 'CABLE COMP B THROTTLE K1A',
                'number' => '17920-K1A -N020-M2',
                'photo' => 'component.png', // Replace with actual photo filename
                'description' => 'Description for Component',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ]
        ];

        foreach ($datas as $data) {
            PartComponent::create($data);
        }
    }
}
