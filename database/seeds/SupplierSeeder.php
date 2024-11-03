<?php

use App\Supplier;
use App\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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
        $faker = Faker::create();
        for ($i = 0; $i < 50; $i++) {
            $supplier = new Supplier;
            $supplier->code = (string)random_int(100000, 999999);
            $supplier->name = "PT " . $faker->name;
            $supplier->address = $faker->address;
            $supplier->phone = $faker->phoneNumber;
            $supplier->contact = 'Anonim';
            $supplier->email = $faker->unique()->safeEmail;
            $supplier->created_by = 'seeder';
            $supplier->updated_by = 'seeder';
            $supplier->save();

            User::create([
                'username' => $supplier->email,
                'full_name' => $supplier->name,
                'department' => 'Supplier',
                'phone_number' => $supplier->phone,
                'npk' => $supplier->code,
                'email' => $supplier->email,
                'password' => Hash::make('password'),
                'type' => 2,
                'supplier_code' => $supplier->code,
                'supplier_name' => $supplier->name,
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
                'role_id' => 3,
                'role_name' => 'Supplier'
            ]);
        }
    }
}
