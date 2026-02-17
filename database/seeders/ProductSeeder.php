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
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Heavy-duty excavator for construction projects',
                'image' => 'assets/images/products/1.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Bulldozer Komatsu D65',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Powerful bulldozer for earthmoving operations',
                'image' => 'assets/images/products/2.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Crane Mobile 50 Ton',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Mobile crane for lifting heavy loads',
                'image' => 'assets/images/products/3.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Concrete Mixer Truck',
                'category_id' => $categories->where('name', 'Transportation')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Ready-mix concrete delivery truck',
                'image' => 'assets/images/products/4.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Generator 100 KVA',
                'category_id' => $categories->where('name', 'Industrial Tools')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Diesel generator for power supply',
                'image' => 'assets/images/products/5.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Helmet Set',
                'category_id' => $categories->where('name', 'Safety Equipment')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Complete safety helmet set for workers',
                'image' => 'assets/images/products/6.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Forklift 5 Ton',
                'category_id' => $categories->where('name', 'Heavy Machinery')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'Industrial forklift for material handling',
                'image' => 'assets/images/products/7.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Compressor Air 200 CFM',
                'category_id' => $categories->where('name', 'Industrial Tools')->first()->id,
                'daily_price' => 20.00,
                'weekly_price' => 15.00,
                'monthly_price' => 10.00,
                'description' => 'High-capacity air compressor',
                'image' => 'assets/images/products/8.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
