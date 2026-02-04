<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductItemCertification;
use App\Models\ProductItem;

class ProductItemCertificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productItems = ProductItem::all();

        if ($productItems->isEmpty()) {
            $this->command->warn('No product items found. Please run ProductItemSeeder first.');
            return;
        }

        $certifications = [
            [
                'product_item_id' => $productItems->where('series_number', 'CAT320D-001')->first()->id,
                'start_date' => '2024-01-15',
                'end_date' => '2027-01-15',
                'file' => 'cert_iso9001_cat320d001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'CAT320D-001')->first()->id,
                'start_date' => '2024-02-01',
                'end_date' => '2029-02-01',
                'file' => 'cert_ce_cat320d001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'KOMD65-001')->first()->id,
                'start_date' => '2024-01-20',
                'end_date' => '2027-01-20',
                'file' => 'cert_iso14001_komd65001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'CRN50T-001')->first()->id,
                'start_date' => '2024-03-01',
                'end_date' => '2025-03-01',
                'file' => 'cert_safety_crn50t001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'CMT-001')->first()->id,
                'start_date' => '2024-01-10',
                'end_date' => '2025-01-10',
                'file' => 'cert_vehicle_cmt001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'GEN100K-001')->first()->id,
                'start_date' => '2024-02-15',
                'end_date' => '2026-02-15',
                'file' => 'cert_electrical_gen100k001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'SHS-001')->first()->id,
                'start_date' => '2024-01-05',
                'end_date' => '2027-01-05',
                'file' => 'cert_safety_shs001.pdf',
            ],
            [
                'product_item_id' => $productItems->where('series_number', 'FL5T-001')->first()->id,
                'start_date' => '2024-03-10',
                'end_date' => '2025-03-10',
                'file' => 'cert_forklift_fl5t001.pdf',
            ],
        ];

        foreach ($certifications as $certification) {
            ProductItemCertification::create($certification);
        }
    }
}
