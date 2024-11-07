<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 15; $i++)
        {
            $title = $faker->sentence(3);

            Post::query()->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'content' => implode("\n\n", $faker->paragraphs(10)),
                'status' => 'published',
                'user_id' => User::query()->inRandomOrder()->first()->id,
                'category_id' => Category::query()->inRandomOrder()->first()->id,
            ]);
        }
    }
}
