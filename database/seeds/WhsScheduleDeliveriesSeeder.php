<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhsScheduleDeliveriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('whs_schedule_deliveries')->insert([
            [
                'customer_id' => 'CUST001',
                'customer_name' => 'PT. ASTRA HONDA MOTOR',
                'customer_plant' => 'Plant A',
                'part_type' => '2W',
                'schedule_date' => Carbon::now()->toDateString(),
                'part_id' => null,
                'part_number' => '77240-K1A -N810-M1',
                'part_name' => 'CABLE COMP, SEAT LOCK K1AL',
                'cycle' => 'C1',
                'qty' => 100,
                'planning_time' => Carbon::now()->addDays(1)->toDateTimeString(),
                'on_time' => Carbon::now()->addDays(2)->toDateTimeString(),
                'delay' => Carbon::now()->addDays(3)->toDateTimeString(),
                'status_prod' => Carbon::now()->addDays(4)->toDateTimeString(),
                'status_qc' => Carbon::now()->addDays(5)->toDateTimeString(),
                'status_spa' => Carbon::now()->addDays(6)->toDateTimeString(),
                'status_ok' => Carbon::now()->addDays(7)->toDateTimeString(),
                'status_ready_to_delivery' => Carbon::now()->addDays(8)->toDateTimeString(),
                'status_delivery' => Carbon::now()->addDays(9)->toDateTimeString(),
            ],
            // [
            //     'customer_id' => 'CUST002',
            //     'customer_name' => 'Customer Two',
            //     'customer_plant' => 'Plant B',
            //     'part_type' => 'Type B',
            //     'schedule_date' => now(),
            //     'part_id' => 'PART002',
            //     'part_number' => 'PN002',
            //     'part_name' => 'Part Two',
            //     'cycle' => 'Cycle 2',
            //     'qty' => 200,
            //     'planning_time' => now()->addDays(1),
            //     'on_time' => now()->addDays(2),
            //     'delay' => now()->addDays(3),
            //     'status_prod' => now()->addDays(4),
            //     'status_qc' => now()->addDays(5),
            //     'status_spa' => now()->addDays(6),
            //     'status_ok' => now()->addDays(7),
            //     'status_ready_to_delivery' => now()->addDays(8),
            //     'status_delivery' => now()->addDays(9),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // Add more seed data as needed
        ]);
    }
}
