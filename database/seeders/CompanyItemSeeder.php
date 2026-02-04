<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompanyItem;
use App\Models\Company;
use App\Models\ProductItem;

class CompanyItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $productItems = ProductItem::all();

        if ($companies->isEmpty() || $productItems->isEmpty()) {
            $this->command->warn('No companies or product items found. Please run CompanySeeder and ProductItemSeeder first.');
            return;
        }

        $companyItems = [
            // Arabian Construction Company items
            [
                'company_id' => $companies->where('name', 'Arabian Construction Company')->first()->id,
                'item_id' => $productItems->where('series_number', 'CAT320D-001')->first()->id,
                'daily_price' => '1500.00',
                'weekly_price' => '9000.00',
                'monthly_price' => '35000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Arabian Construction Company')->first()->id,
                'item_id' => $productItems->where('series_number', 'KOMD65-001')->first()->id,
                'daily_price' => '1200.00',
                'weekly_price' => '7200.00',
                'monthly_price' => '28000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Arabian Construction Company')->first()->id,
                'item_id' => $productItems->where('series_number', 'CMT-001')->first()->id,
                'daily_price' => '800.00',
                'weekly_price' => '4800.00',
                'monthly_price' => '18000.00',
            ],
            // Saudi Heavy Equipment Ltd items
            [
                'company_id' => $companies->where('name', 'Saudi Heavy Equipment Ltd')->first()->id,
                'item_id' => $productItems->where('series_number', 'CAT320D-002')->first()->id,
                'daily_price' => '1500.00',
                'monthly_price' => '35000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Saudi Heavy Equipment Ltd')->first()->id,
                'item_id' => $productItems->where('series_number', 'KOMD65-002')->first()->id,
                'daily_price' => '1200.00',
                'monthly_price' => '28000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Saudi Heavy Equipment Ltd')->first()->id,
                'item_id' => $productItems->where('series_number', 'CRN50T-001')->first()->id,
                'daily_price' => '2000.00',
                'monthly_price' => '45000.00',
            ],
            // Gulf Machinery Rentals items
            [
                'company_id' => $companies->where('name', 'Gulf Machinery Rentals')->first()->id,
                'item_id' => $productItems->where('series_number', 'CAT320D-003')->first()->id,
                'daily_price' => '1500.00',
                'weekly_price' => '9000.00',
                'monthly_price' => '35000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Gulf Machinery Rentals')->first()->id,
                'item_id' => $productItems->where('series_number', 'CRN50T-002')->first()->id,
                'daily_price' => '2000.00',
                'weekly_price' => '12000.00',
                'monthly_price' => '45000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Gulf Machinery Rentals')->first()->id,
                'item_id' => $productItems->where('series_number', 'CMT-002')->first()->id,
                'daily_price' => '800.00',
                'weekly_price' => '4800.00',
                'monthly_price' => '18000.00',
            ],
            // Desert Equipment Solutions items
            [
                'company_id' => $companies->where('name', 'Desert Equipment Solutions')->first()->id,
                'item_id' => $productItems->where('series_number', 'GEN100K-001')->first()->id,
                'daily_price' => '500.00',
                'monthly_price' => '11000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Desert Equipment Solutions')->first()->id,
                'item_id' => $productItems->where('series_number', 'FL5T-001')->first()->id,
                'daily_price' => '600.00',
                'monthly_price' => '13500.00',
            ],
            [
                'company_id' => $companies->where('name', 'Desert Equipment Solutions')->first()->id,
                'item_id' => $productItems->where('series_number', 'CA200C-001')->first()->id,
                'daily_price' => '300.00',
                'monthly_price' => '6500.00',
            ],
            // Royal Construction Services items
            [
                'company_id' => $companies->where('name', 'Royal Construction Services')->first()->id,
                'item_id' => $productItems->where('series_number', 'GEN100K-002')->first()->id,
                'daily_price' => '500.00',
                'weekly_price' => '3000.00',
                'monthly_price' => '11000.00',
            ],
            [
                'company_id' => $companies->where('name', 'Royal Construction Services')->first()->id,
                'item_id' => $productItems->where('series_number', 'FL5T-002')->first()->id,
                'daily_price' => '600.00',
                'weekly_price' => '3600.00',
                'monthly_price' => '13500.00',
            ],
            [
                'company_id' => $companies->where('name', 'Royal Construction Services')->first()->id,
                'item_id' => $productItems->where('series_number', 'CA200C-002')->first()->id,
                'daily_price' => '300.00',
                'weekly_price' => '1800.00',
                'monthly_price' => '6500.00',
            ],
        ];

        foreach ($companyItems as $companyItem) {
            CompanyItem::create($companyItem);
        }
    }
}
