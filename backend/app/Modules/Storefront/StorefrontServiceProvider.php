<?php

namespace App\Modules\Storefront;

use App\Modules\Storefront\Services\StorefrontSettings;
use Illuminate\Support\ServiceProvider;

class StorefrontServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Production refuses to start with a policy value unset (backend-architecture §12, research R-01).
        if ($this->app->isProduction()) {
            $this->app->make(StorefrontSettings::class)->assertComplete();
        }
    }
}
