<?php

use Illuminate\Database\Seeder;
use App\SegmentRack;

class SegmentRackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SegmentRack::create([
            'plant' => '1601',
            'code' => '1601-RW-A',
            'slock' => 'RAW01',
        ]);

        SegmentRack::create([
            'plant' => '1601',
            'code' => '1601-RW-B',
            'slock' => 'RAW01',
        ]);
    }
}
