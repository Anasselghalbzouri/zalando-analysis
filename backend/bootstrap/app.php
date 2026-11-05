<?php

use App\Support\Http\Middleware\AddServerTime;
use App\Support\Http\Middleware\AssignRequestId;
use App\Support\Http\Middleware\EnsureJsonBody;
use App\Support\Problems\ProblemRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // REST API under /api/v1 (api-contracts §1). Module route files are included from routes/api.php.
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        // Framework health route; AppServiceProvider adds a database check (docker-architecture §6).
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global, so unmatched /api/v1 routes get a request ID too (research R-05).
        $middleware->prepend(AssignRequestId::class);
        $middleware->appendToGroup('api', [EnsureJsonBody::class, AddServerTime::class]);

        // Route groups by auth code (api-contracts §1, research R-08). Every /api/v1 route is in exactly one.
        $middleware->group('access.session', []); // S: session required, filled by US-001/US-002
        $middleware->group('access.catalog', []); // C: catalogue access, filled by US-005 (catalog.access)
        $middleware->group('access.public', []);  // P: public, no guard
        $middleware->group('access.guest', []);   // G: guest only, filled by US-001/US-002
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every /api/v1 error as application/problem+json (error-format.md).
        $exceptions->render(new ProblemRenderer);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
