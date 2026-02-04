<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompanyPriceList;
use App\Models\Company;
use App\Models\Product;

class CompanyPriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $products = Product::all();

        if ($companies->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No companies or products found. Please run CompanySeeder and ProductSeeder first.');
            return;
        }

        // Create price lists for each company with different products
        foreach ($companies as $company) {
            // Select random products for each company (3-5 products per company)
            $selectedProducts = $products->random(rand(3, 5));

            foreach ($selectedProducts as $product) {
                $pricingType = $company->pricing_type;

                $priceData = [
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'pricing_type' => $pricingType,
                    'daily_price' => $product->daily_price * (0.8 + (rand(0, 40) / 100)), // 80-120% of base price
                    'monthly_price' => $product->monthly_price * (0.8 + (rand(0, 40) / 100)),
                    'is_active' => true,
                ];

                // Add weekly price only if company supports it
                if ($pricingType === 'daily_weekly_monthly') {
                    $priceData['weekly_price'] = $product->weekly_price * (0.8 + (rand(0, 40) / 100));
                }

                CompanyPriceList::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                    ],
                    $priceData
                );
            }
        }
    }
}
