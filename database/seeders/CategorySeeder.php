<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Construction Equipment',
                'is_active' => true,
            ],
            [
                'name' => 'Heavy Machinery',
                'is_active' => true,
            ],
            [
                'name' => 'Transportation',
                'is_active' => true,
            ],
            [
                'name' => 'Industrial Tools',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Equipment',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
