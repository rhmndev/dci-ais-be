<?php

use App\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::truncate();
        $datas = [
            [
                'code' => 'C000114',
                'name' => 'PT AHM',
                'address' => 'Jl. Industri',
                'phone' => '0212222233333',
                'email' => 'ahm@astra.email',
                'contact' => 'Bpk. Astra',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'code' => 'C000116',
                'name' => 'PT CHEMCO',
                'address' => 'JL. Industri II',
                'phone' => '021939495591',
                'email' => 'staff@chemco.email',
                'contact' => 'Handoyo',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ]
        ];

        foreach ($datas as $data) {

            $customer = Customer::firstOrNew(['code' => $data['code']]);
            $customer->code = $data['code'];
            $customer->name = $data['name'];
            $customer->address = $data['address'];
            $customer->phone = $data['phone'];
            $customer->email = $data['email'];
            $customer->contact = $data['contact'];
            $customer->created_by = $data['created_by'];
            $customer->updated_by = $data['updated_by'];
            $customer->save();
        }
    }
}
