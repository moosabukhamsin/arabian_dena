<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-users', fn (User $user) => $user->canAccessUsers());
        Gate::define('access-timesheets', fn (User $user) => $user->canAccessTimesheets());
        Gate::define('modify-inventory', fn (User $user) => $user->canModifyInventory());
        Gate::define('create-company', fn (User $user) => $user->canCreateCompany());
        Gate::define('access-company-pricing', fn (User $user) => $user->canAccessCompanyPricing());
        Gate::define('delete-records', fn (User $user) => $user->canDeleteRecords());
    }
}
