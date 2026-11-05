<?php

namespace App\Support\Http\Middleware;

use App\Support\RequestClock;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Adds meta.server_time to every successful {data, meta} envelope (api-contracts §1, FR-013). */
class AddServerTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && $response->isSuccessful()) {
            $payload = $response->getData(true);

            if (is_array($payload) && array_key_exists('data', $payload)) {
                $payload['meta']['server_time'] = RequestClock::now($request)->toIso8601ZuluString();
                $response->setData($payload);
            }
        }

        return $response;
    }
}
