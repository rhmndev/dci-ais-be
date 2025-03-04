<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerScheduleDeliveryListsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer_schedule_delivery_lists')->insert([
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'customer_alias' => 'P1P1',
                'customer_image' => 'https://career.untar.ac.id/images/logo/13663851656PT_ASTRA_HONDA_MOTOR_.png',
                'part_no' => '17910-K1A -N020-M2',
                'part_name' => 'CABLE COMP A THROTTLE K1AA',
                'part_type' => '2W',
                'show' => true,
            ],
            [
                'customer_id' => 'CUST002',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant B',
                'customer_alias' => 'P2P1',
                'customer_image' => 'https://career.untar.ac.id/images/logo/13663851656PT_ASTRA_HONDA_MOTOR_.png',
                'part_no' => '17920-K1A -N020-M2',
                'part_name' => 'CABLE COMP B THROTTLE K1AA',
                'part_type' => '2W',
                'show' => true,
            ],
            // Add more seed data as needed
        ]);
    }
}
