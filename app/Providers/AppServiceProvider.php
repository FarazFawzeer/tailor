<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Support\Permissions;

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
        //
         Paginator::useBootstrap(); 

        // Super admin can do everything, whatever permissions are ticked
        Gate::before(function ($user, $ability) {
            return $user->hasRole(Permissions::SUPER_ADMIN) ? true : null;
        });
         
    }
}
