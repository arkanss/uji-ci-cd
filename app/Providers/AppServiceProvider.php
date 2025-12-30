<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ProductDistribution;
use App\Observers\ProductDistributionObserver;

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
        ProductDistribution::observe(ProductDistributionObserver::class);
    }
}
