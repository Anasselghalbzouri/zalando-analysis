<?php

namespace App\Support\Http\Middleware;

use App\Support\Http\RequestId;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/** Global: one request ID per request, in the log context and the response header (NFR-OBS-01). */
class AssignRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = RequestId::for($request);
        Context::add('request_id', $id);

        $response = $next($request);
        $response->headers->set(RequestId::HEADER, $id);

        return $response;
    }
}
