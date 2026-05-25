<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'email_verified_at' => 'datetime',
        ];
    }

    public function roleLabel(): string
    {
        return $this->role?->label() ?? UserRole::Engineer->label();
    }

    public function canAccessUsers(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::OperationHead], true);
    }

    public function canAccessTimesheets(): bool
    {
        return $this->role !== UserRole::Engineer;
    }

    public function canModifyInventory(): bool
    {
        return $this->role !== UserRole::Finance;
    }

    public function canCreateCompany(): bool
    {
        return $this->role !== UserRole::Engineer;
    }

    public function canAccessCompanyPricing(): bool
    {
        return $this->role !== UserRole::Engineer;
    }

    public function canDeleteRecords(): bool
    {
        return ! in_array($this->role, [UserRole::Engineer, UserRole::WarehouseCoordinator], true);
    }

    public function canApprovePending(): bool
    {
        return in_array($this->role, [
            UserRole::Admin,
            UserRole::OperationHead,
            UserRole::OperationCoordinator,
        ], true);
    }
}
