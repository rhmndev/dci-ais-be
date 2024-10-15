<?php

use App\Supplier;
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
            Supplier::create([
                'code' => random_int(100000, 999999),
                'name' => "PT " . $faker->name,
                'address' => $faker->address,
                'phone' => $faker->phoneNumber,
                'contact' => 'Anonim',
                'email' => $faker->unique()->safeEmail,
                'created_by' => 'seeder',
                'updated_by' =>  'seeder'
            ]);
        }
    }
}
