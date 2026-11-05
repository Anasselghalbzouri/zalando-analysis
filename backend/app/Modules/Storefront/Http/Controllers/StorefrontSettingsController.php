<?php

namespace App\Modules\Storefront\Http\Controllers;

use App\Modules\Storefront\Services\StorefrontSettings;
use App\Support\Http\ApiFormRequest;
use Illuminate\Http\JsonResponse;

/** GET /api/v1/storefront/settings (api-contracts §3): the policy values the SPA needs. */
class StorefrontSettingsController
{
    public function __invoke(ApiFormRequest $request, StorefrontSettings $settings): JsonResponse
    {
        return response()->json(['data' => $settings->toPublicArray()])->header('Cache-Control', 'no-store');
    }
}
