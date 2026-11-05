<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_tests_never_use_the_development_database(): void
    {
        // phpunit.xml forces an in-memory database: compose injects the dev MySQL credentials into the container.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_the_health_route_responds(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_unknown_api_routes_answer_json_under_the_v1_prefix(): void
    {
        $this->get('/api/v1/unknown-route')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json') // US-059: error-format.md
            ->assertJsonPath('code', 'not_found');
    }
}
