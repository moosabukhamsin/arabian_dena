<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case OperationHead = 'operation_head';
    case OperationCoordinator = 'operation_coordinator';
    case WarehouseCoordinator = 'warehouse_coordinator';
    case Engineer = 'engineer';
    case Finance = 'finance';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::OperationHead => 'Operation Head',
            self::OperationCoordinator => 'Operation Coordinator',
            self::WarehouseCoordinator => 'Warehouse Coordinator',
            self::Engineer => 'Engineer',
            self::Finance => 'Finance',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
