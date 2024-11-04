<?php

use App\ShippingAddress;
use Illuminate\Database\Seeder;

class ShippingAdressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShippingAddress::truncate();

        $data = [
            ['full_address' => 'Jl.Tekno Industri kawasan Industri Jababeka VIII No.1 Blok A3, Cikarang Kota, Kec. Cikarang Utara,
                Kabupaten Bekasi, Jawa Barat'],
        ];

        foreach ($data as $item) {
            ShippingAddress::create($item);
        }
    }
}
