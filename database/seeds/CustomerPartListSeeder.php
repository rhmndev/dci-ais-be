<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerPartListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer_part_lists')->insert([
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'customer_image' => '',
                'part_no' => 'PART001',
                'part_name' => 'Part One',
                'part_type' => 'Type A',
                'show' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'customer_image' => '',
                'part_no' => 'PART002',
                'part_name' => 'Part Two',
                'part_type' => 'Type B',
                'show' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more seed data as needed
        ]);
    }
}
