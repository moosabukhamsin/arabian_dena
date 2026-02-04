<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CompanyEmployee;
use App\Models\Company;

class CompanyEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found. Please run CompanySeeder first.');
            return;
        }

        $employees = [
            [
                'company_id' => $companies->where('name', 'Arabian Construction Company')->first()->id,
                'name' => 'Ahmed Al-Rashid',
                'email' => 'ahmed@arabianconstruction.com',
                'mobile_number' => '+966501111111',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Arabian Construction Company')->first()->id,
                'name' => 'Mohammed Al-Sheikh',
                'email' => 'mohammed@arabianconstruction.com',
                'mobile_number' => '+966501111112',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Saudi Heavy Equipment Ltd')->first()->id,
                'name' => 'Omar Al-Mansouri',
                'email' => 'omar@saudiheavy.com',
                'mobile_number' => '+966502222221',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Saudi Heavy Equipment Ltd')->first()->id,
                'name' => 'Khalid Al-Zahrani',
                'email' => 'khalid@saudiheavy.com',
                'mobile_number' => '+966502222222',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Gulf Machinery Rentals')->first()->id,
                'name' => 'Fahad Al-Ghamdi',
                'email' => 'fahad@gulfmachinery.com',
                'mobile_number' => '+966503333331',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Desert Equipment Solutions')->first()->id,
                'name' => 'Saeed Al-Otaibi',
                'email' => 'saeed@desertequipment.com',
                'mobile_number' => '+966504444441',
                'is_active' => true,
            ],
            [
                'company_id' => $companies->where('name', 'Royal Construction Services')->first()->id,
                'name' => 'Abdulrahman Al-Sulaimani',
                'email' => 'abdulrahman@royalconstruction.com',
                'mobile_number' => '+966505555551',
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employee) {
            CompanyEmployee::create($employee);
        }
    }
}
