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

            $vendor = Vendor::firstOrNew(['code' => $data['code']]);
            $vendor->code = $data['code'];
            $vendor->name = $data['name'];
            $vendor->address = $data['address'];
            $vendor->phone = $data['phone'];
            $vendor->email = $data['email'];
            $vendor->contact = $data['contact'];
            $vendor->created_by = $data['created_by'];
            $vendor->updated_by = $data['updated_by'];
            $vendor->save();

        }
    }
}
