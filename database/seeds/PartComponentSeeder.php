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
                'customer_id' => '6704b08578470000fc005ce2', // Replace with actual customer ID
                'name' => 'CABLE COMP A THROTTLE K1A',
                'number' => '17910-K1A -N020-M2',
                'photo' => 'component.jpg', // Replace with actual photo filename
                'description' => 'Description for Component',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'customer_id' => '6704b08578470000fc005ce2', // Replace with actual customer ID
                'name' => 'CABLE COMP B THROTTLE K1A',
                'number' => '17920-K1A -N020-M2',
                'photo' => 'component.png', // Replace with actual photo filename
                'description' => 'Description for Component',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'customer_id' => '6704b08578470000fc005ce3', // Replace with actual customer ID
                'name' => 'CABLE COMP X',
                'number' => 'X-K1A -B-M2',
                'photo' => 'component.jpg', // Replace with actual photo filename
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
