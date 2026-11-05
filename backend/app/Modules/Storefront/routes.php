<?php

// Storefront module routes (api-contracts §3). Required from routes/api.php: already in the api group under /api/v1.

use App\Modules\Storefront\Http\Controllers\StorefrontSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('access.public')
    ->get('storefront/settings', StorefrontSettingsController::class)
    ->name('storefront.settings');
