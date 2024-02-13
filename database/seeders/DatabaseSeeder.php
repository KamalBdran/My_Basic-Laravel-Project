<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
    $faker = Faker::create();
    $password = Hash::make('password123');
     foreach (range(1,10) as $index)
     DB::table('users')->insert([
       'first_name' => $faker->name,
        'last_name'=>$faker->name,
        'password' => $password,
        ]);
        
    }
}
