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
    }
}
