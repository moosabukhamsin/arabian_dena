<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Excavator CAT 320D',
                'category_id' => $categories->where('name', 'Construction Equipment')->first()->id,
                'daily_price' => 1500.00,
                'weekly_price' => 9000.00,
                'monthly_price' => 35000.00,
                'description' => 'Heavy-duty excavator for construction projects',
                'is_active' => true,
            ],
            [
                'name' => 'Bulldozer Komatsu D65',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 1200.00,
                'weekly_price' => 7200.00,
                'monthly_price' => 28000.00,
                'description' => 'Powerful bulldozer for earthmoving operations',
                'is_active' => true,
            ],
            [
                'name' => 'Crane Mobile 50 Ton',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 2000.00,
                'weekly_price' => 12000.00,
                'monthly_price' => 45000.00,
                'description' => 'Mobile crane for lifting heavy loads',
                'is_active' => true,
            ],
            [
                'name' => 'Concrete Mixer Truck',
                'category_id' => $categories->where('name', 'Transportation')->first()->id,
                'daily_price' => 800.00,
                'weekly_price' => 4800.00,
                'monthly_price' => 18000.00,
                'description' => 'Ready-mix concrete delivery truck',
                'is_active' => true,
            ],
            [
                'name' => 'Generator 100 KVA',
                'category_id' => $categories->where('name', 'Industrial Tools')->first()->id,
                'daily_price' => 500.00,
                'weekly_price' => 3000.00,
                'monthly_price' => 11000.00,
                'description' => 'Diesel generator for power supply',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Helmet Set',
                'category_id' => $categories->where('name', 'Safety Equipment')->first()->id,
                'daily_price' => 25.00,
                'weekly_price' => 150.00,
                'monthly_price' => 500.00,
                'description' => 'Complete safety helmet set for workers',
                'is_active' => true,
            ],
            [
                'name' => 'Forklift 5 Ton',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 600.00,
                'weekly_price' => 3600.00,
                'monthly_price' => 13500.00,
                'description' => 'Industrial forklift for material handling',
                'is_active' => true,
            ],
            [
                'name' => 'Compressor Air 200 CFM',
                'category_id' => $categories->where('name', 'Industrial Tools')->first()->id,
                'daily_price' => 300.00,
                'weekly_price' => 1800.00,
                'monthly_price' => 6500.00,
                'description' => 'High-capacity air compressor',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
