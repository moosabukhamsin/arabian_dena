<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Arabian Construction Company',
                'email' => 'info@arabianconstruction.com',
                'mobile_number' => '+966501234567',
                'pricing_type' => 'daily_weekly_monthly',
                'image' => 'assets/images/users/1.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Heavy Equipment Ltd',
                'email' => 'contact@saudiheavy.com',
                'mobile_number' => '+966502345678',
                'pricing_type' => 'daily_monthly',
                'image' => 'assets/images/users/2.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Gulf Machinery Rentals',
                'email' => 'rentals@gulfmachinery.com',
                'mobile_number' => '+966503456789',
                'pricing_type' => 'daily_weekly_monthly',
                'image' => 'assets/images/users/3.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Desert Equipment Solutions',
                'email' => 'solutions@desertequipment.com',
                'mobile_number' => '+966504567890',
                'pricing_type' => 'daily_monthly',
                'image' => 'assets/images/users/4.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Royal Construction Services',
                'email' => 'services@royalconstruction.com',
                'mobile_number' => '+966505678901',
                'pricing_type' => 'daily_weekly_monthly',
                'image' => 'assets/images/users/5.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
