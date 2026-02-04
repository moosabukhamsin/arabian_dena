<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Run seeders in order
        $this->call([
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
