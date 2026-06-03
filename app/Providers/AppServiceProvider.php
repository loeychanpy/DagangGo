<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.navigation', function ($view) {
            $lowStockCount = 0;
            if (auth()->check()) {
                $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();
            }
            $view->with('lowStockCount', $lowStockCount);
        });
    }
}
