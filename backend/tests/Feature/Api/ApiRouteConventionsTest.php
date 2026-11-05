<?php

namespace Tests\Feature\Api;

use App\Support\Http\ApiFormRequest;
use Closure;
use Illuminate\Support\Facades\Route;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\TestCase;

/** Research R-04, SC-006: every /api/v1 action declares its query parameters through an ApiFormRequest. */
class ApiRouteConventionsTest extends TestCase
{
    public function test_every_api_action_type_hints_an_api_form_request(): void
    {
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1')) {
                continue;
            }

            $uses = $route->getAction('uses');
            $reflection = $uses instanceof Closure
                ? new ReflectionFunction($uses)
                : new ReflectionMethod(...(str_contains($uses, '@') ? explode('@', $uses) : [$uses, '__invoke']));

            $typed = array_filter($reflection->getParameters(), fn ($p) => $p->getType() instanceof ReflectionNamedType
                && is_a($p->getType()->getName(), ApiFormRequest::class, true));

            $this->assertNotEmpty($typed, "Route [{$route->uri()}] must type-hint an ApiFormRequest.");
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'No /api/v1 route was found: the URI filter is wrong.');
    }
}
