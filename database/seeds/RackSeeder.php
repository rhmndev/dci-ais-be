<?php

use App\Rack;
use App\SegmentRack;
use App\SLock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sLocks = SLock::where('code', 'like', '%RAW01%')->get();
        $segments = SegmentRack::all();

        foreach ($sLocks as $sLock) {
            foreach ($segments as $segment) {
                for ($i = 1; $i <= 4; $i++) {
                    for ($j = 1; $j <= 3; $j++) {
                        for ($k = 1; $k <= 2; $k++) {
                            Rack::create([
                                'code' => $segment->code . '.' . $sLock->code . '.' . $i . '.' . $j . '.' . $k,
                                'code_slock' => $sLock->code,
                                'name' =>  $i . '.' . $j . '.' . $k,
                                'segment' => $segment->code,
                                'position' =>  $i . '.' . $j . '.' . $k,
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
