<?php

use App\Part;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Part::class, 50)->create()->each(function ($part) {
            $part->generateQRCode();
        });
    }
}
