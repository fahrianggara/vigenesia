<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $categories = [
            [
                'name' => 'Tren Digital',
                'slug' => 'tren-digital',
                'description' => $faker->paragraph(2),
            ],
            [
                'name' => 'Dunia Dev & Coding',
                'slug' => 'dunia-dev-coding',
                'description' => $faker->paragraph(2),
            ],
            [
                'name' => 'Inovasi & Startup',
                'slug' => 'inovasi-startup',
                'description' => $faker->paragraph(2),
            ],
            [
                'name' => 'Blockchain & Kripto',
                'slug' => 'blockchain-kripto',
                'description' => $faker->paragraph(2),
            ]
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
