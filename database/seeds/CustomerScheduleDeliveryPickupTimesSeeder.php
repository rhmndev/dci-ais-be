<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerScheduleDeliveryPickupTimesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'pickup_time' => Carbon::now()->subDays(1)->toDateTimeString(),
                'type' => 'Planning',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ];

        DB::table('customer_schedule_delivery_pickup_times')->insert($data);
    }
}
