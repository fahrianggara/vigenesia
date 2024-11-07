<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Fahri Anggara',
            'email' => 'fahrianggara@mail.com',
            'username' => 'fahrianggara',
            'password' => 'password',
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $faker = \Faker\Factory::create('id_ID');

        for ($i = 0; $i < 2; $i++) {
            User::query()->create([
                'name' => $faker->name,
                'email' => $faker->email,
                'username' => $faker->userName,
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}
