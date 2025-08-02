<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VocabularyCategory as Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::updateOrCreate([
            'name' => 'Noun',
            'color_code' => '#FFC107'
        ]);

        Category::updateOrCreate([
            'name' => 'Adjective',
            'color_code' => '#EE47FF'
        ]);

        Category::updateOrCreate([
            'name' => 'Adverb',
            'color_code' => '#0F68DC'
        ]);

        Category::updateOrCreate([
            'name' => 'Verb',
            'color_code' => '#50BFA5'
        ]);
    }
}
