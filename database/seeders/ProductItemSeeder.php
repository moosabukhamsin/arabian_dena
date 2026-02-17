<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductItem;
use App\Models\Product;

class ProductItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        $productItems = [
            // Excavator CAT 320D items
            [
                'product_id' => $products->where('name', 'Excavator CAT 320D')->first()->id,
                'series_number' => 'CAT320D-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Excavator CAT 320D')->first()->id,
                'series_number' => 'CAT320D-002',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Excavator CAT 320D')->first()->id,
                'series_number' => 'CAT320D-003',
                'is_active' => true,
            ],
            // Bulldozer Komatsu D65 items
            [
                'product_id' => $products->where('name', 'Bulldozer Komatsu D65')->first()->id,
                'series_number' => 'KOMD65-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Bulldozer Komatsu D65')->first()->id,
                'series_number' => 'KOMD65-002',
                'is_active' => true,
            ],
            // Crane Mobile 50 Ton items
            [
                'product_id' => $products->where('name', 'Crane Mobile 50 Ton')->first()->id,
                'series_number' => 'CRN50T-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Crane Mobile 50 Ton')->first()->id,
                'series_number' => 'CRN50T-002',
                'is_active' => true,
            ],
            // Concrete Mixer Truck items
            [
                'product_id' => $products->where('name', 'Concrete Mixer Truck')->first()->id,
                'series_number' => 'CMT-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Concrete Mixer Truck')->first()->id,
                'series_number' => 'CMT-002',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Concrete Mixer Truck')->first()->id,
                'series_number' => 'CMT-003',
                'is_active' => true,
            ],
            // Generator 100 KVA items
            [
                'product_id' => $products->where('name', 'Generator 100 KVA')->first()->id,
                'series_number' => 'GEN100K-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Generator 100 KVA')->first()->id,
                'series_number' => 'GEN100K-002',
                'is_active' => true,
            ],
            // Safety Helmet Set items
            [
                'product_id' => $products->where('name', 'Safety Helmet Set')->first()->id,
                'series_number' => 'SHS-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Safety Helmet Set')->first()->id,
                'series_number' => 'SHS-002',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Safety Helmet Set')->first()->id,
                'series_number' => 'SHS-003',
                'is_active' => true,
            ],
            // Forklift 5 Ton items
            [
                'product_id' => $products->where('name', 'Forklift 5 Ton')->first()->id,
                'series_number' => 'FL5T-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Forklift 5 Ton')->first()->id,
                'series_number' => 'FL5T-002',
                'is_active' => true,
            ],
            // Compressor Air 200 CFM items
            [
                'product_id' => $products->where('name', 'Compressor Air 200 CFM')->first()->id,
                'series_number' => 'CA200C-001',
                'is_active' => true,
            ],
            [
                'product_id' => $products->where('name', 'Compressor Air 200 CFM')->first()->id,
                'series_number' => 'CA200C-002',
                'is_active' => true,
            ],
        ];

        foreach ($productItems as $productItem) {
            $productItem['status'] = 'In Stock';
            ProductItem::create($productItem);
        }
    }
}
