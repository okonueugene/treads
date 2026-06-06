<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('access-vendor-dashboard', fn (User $user) => $user->is_vendor);

        View::composer('layouts.navigation', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
