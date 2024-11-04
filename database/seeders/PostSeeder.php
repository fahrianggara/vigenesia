<?php

namespace Database\Seeders;

use App\Models\Post;
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

        for ($i = 0; $i < 5; $i++)
        {
            $title = $faker->sentence(3);

            Post::query()->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'content' => implode("\n\n", $faker->paragraphs(10)),
                'status' => 'published',
                'user_id' => 1,
                'category_id' => collect([1, 2, 3, 4])->random(),
            ]);
        }
    }
}
