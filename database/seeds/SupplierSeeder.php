<?php

use App\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Supplier::truncate();
        $datas = [
            [
                'name' => 'PT Angkasa',
                'address' => 'Jl Industri',
                'phone' => '+62123123123123',
                'email' => 'angkasa@astra.email',
                'contact' => 'Anonim',
                'created_by' => 'seeder',
                'updated_by' =>  'seeder'
            ],
            [
                'name' => 'PT Putra',
                'address' => 'Jl Industri',
                'phone' => '+62123123123123',
                'email' => 'putra@astra.email',
                'contact' => 'Anonim',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'PT ABC',
                'address' => 'Jl Industri',
                'phone' => '+62123123123123',
                'email' => 'abc@astra.email',
                'contact' => 'Anonim',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {
            $Supplier = new Supplier();
            $Supplier->name = $data['name'];
            $Supplier->address = $data['address'];
            $Supplier->phone = $data['phone'];
            $Supplier->email = $data['email'];
            $Supplier->contact = $data['contact'];
            $Supplier->created_by = $data['created_by'];
            $Supplier->updated_by = $data['updated_by'];
            $Supplier->save();
        }
    }
}
