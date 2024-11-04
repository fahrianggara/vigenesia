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
                'description' => 'Fokus pada tren terbaru dalam teknologi digital, gadget, dan aplikasi.',
            ],
            [
                'name' => 'Dunia Dev & Coding',
                'slug' => 'dunia-dev-coding',
                'description' => ' Berita dan update terbaru dari dunia pengembangan perangkat lunak, framework, dan bahasa pemrograman.',
            ],
            [
                'name' => 'Inovasi & Startup',
                'slug' => 'inovasi-startup',
                'description' => 'Berita tentang startup, inovasi teknologi, dan kewirausahaan di bidang IT.',
            ],
            [
                'name' => 'Blockchain & Kripto',
                'slug' => 'blockchain-kripto',
                'description' => 'Kategori untuk berita tentang blockchain, mata uang kripto, dan teknologi terdesentralisasi lainnya.',
            ],
            [
                'name' => 'Mobile & Gadget',
                'slug' => 'mobile-gadget',
                'description' => 'Fokus pada perkembangan terbaru dari perangkat mobile, smartphone, dan gadget lainnya.',
            ],
            [
                'name' => 'Teknologi Hijau',
                'slug' => 'teknologi-hijau',
                'description' => 'Fokus pada teknologi ramah lingkungan dan inovasi yang mendukung keberlanjutan.',
            ],
            [
                'name' => 'AI & Machine Learning',
                'slug' => 'ai-machine-learning',
                'description' => 'Kategori khusus untuk berita terkait kecerdasan buatan, machine learning, dan aplikasi mereka dalam berbagai bidang.',
            ]
        ];

        foreach ($categories as $category) {
            Category::query()->create($category);
        }
    }
}
