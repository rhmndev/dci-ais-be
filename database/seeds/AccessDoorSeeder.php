<?php

use Illuminate\Database\Seeder;
use App\AccessDoor;
use Carbon\Carbon;

class AccessDoorSeeder extends Seeder
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
                'npk' => '2001001',
                'nama' => 'Rega',
                'clock_in' => '2020-07-28 07:03:57',
                'clock_out' => '2020-07-28 16:02:55',
            ],
            [
                'npk' => '2001002',
                'nama' => 'Yulianto',
                'clock_in' => '2020-07-28 07:05:58',
                'clock_out' => '2020-07-28 16:10:55',
            ],
            [
                'npk' => '2001003',
                'nama' => 'Irfan',
                'clock_in' => '2020-07-28 07:04:11',
                'clock_out' => '2020-07-28 16:05:45',
            ],
        ];

        foreach ($datas as $data) {

            $account = new AccessDoor;
            $account->npk = $data['npk'];
            $account->nama = $data['nama'];
            $account->clock_in = Carbon::parse($data['clock_in']);
            $account->clock_out = Carbon::parse($data['clock_out']);
            $account->save();
            
        }
    }
}
