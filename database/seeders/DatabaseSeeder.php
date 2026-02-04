<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CompanySeeder::class,
            CompanyEmployeeSeeder::class,
            ProductSeeder::class,
            ProductItemSeeder::class,
            ProductItemCertificationSeeder::class,
            CompanyItemSeeder::class,
            CompanyPriceListSeeder::class,
            OrderItemCombinationSeeder::class,
        ]);
    }
}
