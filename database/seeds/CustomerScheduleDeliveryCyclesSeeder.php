<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerScheduleDeliveryCyclesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer_schedule_delivery_cycles')->insert([
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'cycle' => 'C1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'cycle' => 'C2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'cycle' => 'C3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 'CUST002',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant B',
                'customer_alias' => 'P2P1',
                'cycle' => 'C1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 'CUST002',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant B',
                'customer_alias' => 'P2P1',
                'cycle' => 'C2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more seed data as needed
        ]);
    }
}
