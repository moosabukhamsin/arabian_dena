<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@arabian-dena.com',
                'role' => UserRole::Admin,
            ],
            [
                'name' => 'Operation Head',
                'email' => 'operation-head@arabian-dena.com',
                'role' => UserRole::OperationHead,
            ],
            [
                'name' => 'Operation Coordinator',
                'email' => 'operation-coordinator@arabian-dena.com',
                'role' => UserRole::OperationCoordinator,
            ],
            [
                'name' => 'Warehouse Coordinator',
                'email' => 'warehouse-coordinator@arabian-dena.com',
                'role' => UserRole::WarehouseCoordinator,
            ],
            [
                'name' => 'Engineer',
                'email' => 'engineer@arabian-dena.com',
                'role' => UserRole::Engineer,
            ],
            [
                'name' => 'Finance',
                'email' => 'finance@arabian-dena.com',
                'role' => UserRole::Finance,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
