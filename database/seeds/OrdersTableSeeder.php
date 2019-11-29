<?php

use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        DB::table('orders')->insert([
            [
                'order_id' => Str::random(10),
                'current_location' => $faker->streetAddress,
                'last_location' => $faker->streetAddress,
                'status' => "approved",
            ],
            [
                'order_id' => Str::random(10),
                'current_location' => $faker->streetAddress,
                'last_location' => $faker->streetAddress,
                'status' => "delivered",
            ],
            [
                'order_id' => Str::random(10),
                'current_location' => $faker->streetAddress,
                'last_location' => $faker->streetAddress,
                'status' => "in transit",
            ],
            [
                'order_id' => Str::random(10),
                'current_location' => $faker->streetAddress,
                'last_location' => $faker->streetAddress,
                'status' => "awaiting approval",
            ],
        ]);
    }
}
