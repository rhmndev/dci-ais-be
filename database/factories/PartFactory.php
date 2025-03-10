<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Part;
use Faker\Generator as Faker;

$factory->define(Part::class, function (Faker $faker) {
    return [
        'code' => Part::generateNewCode(),
        'name' => $faker->word,
        'description' => $faker->sentence,
        'category_code' => null,
        'qr_code' => null, // QR code will be generated after creation
    ];
});
