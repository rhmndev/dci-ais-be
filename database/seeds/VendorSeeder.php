<?php

use Illuminate\Database\Seeder;
use App\Vendor;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Vendor::truncate();
        $datas = [
            [
                'code' => 'A00012',
                'name' => 'CV ABADI TUNGGAL SEJAHTERA',
                'address' => 'PERUM KOTA SERANG BARU BLOK D-13 NO. 010 RT.003',
                'phone' => '02129612375',
                'email' => 'cv.ats.cikarang@gmail.com',
                'contact' => 'Bpk. Sutrisno',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'code' => 'A00030',
                'name' => 'PT ABBY JAYA TEKNIK',
                'address' => 'JL. PERHUB DARAT VI BLOK A NO. 13 RT 002 RW 011 KEL. CIBUNTU',
                'phone' => '081318330691',
                'email' => 'market_ajt@yahoo.com',
                'contact' => 'Handoko',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {

            $user = Vendor::firstOrNew(['code' => $data['code']]);
            $user->code = $data['code'];
            $user->name = $data['name'];
            $user->address = $data['address'];
            $user->phone = $data['phone'];
            $user->email = $data['email'];
            $user->contact = $data['contact'];
            $user->created_by = $data['created_by'];
            $user->updated_by = $data['updated_by'];
            $user->save();

        }
    }
}
