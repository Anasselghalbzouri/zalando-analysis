<?php

namespace App\Support\Http\Middleware;

use App\Support\Problems\ApiProblem;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API bodies are JSON (api-contracts §1). The framework silently reads invalid JSON as an
 * empty body, so it is rejected here (research R-07, erratum E-01).
 */
class EnsureJsonBody
{
    public function handle(Request $request, Closure $next): Response
    {
        $body = $request->getContent();

        if ($body !== '') {
            if (! $request->isJson()) {
                throw new ApiProblem('unsupported_media_type');
            }
            if (! json_validate($body)) {
                throw new ApiProblem('bad_request');
            }
        }

        return $next($request);
    }
}
