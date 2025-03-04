<?php

use App\SLock;
use Illuminate\Database\Seeder;

class SLockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SLock::truncate();
        $datas = [
            [
                'code' => 'MT01',
                'description' => 'Maintenance',
            ],
            [
                'code' => 'CO01',
                'description' => 'Area B',
            ],
        ];

        foreach ($datas as $data) {

            $Slock = SLock::firstOrNew(['code' => $data['code']]);
            $Slock->code = $data['code'];
            $Slock->description = $data['description'];
            $Slock->save();
        }
    }
}
