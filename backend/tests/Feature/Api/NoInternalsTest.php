<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/** Story AC-1, FR-005: no internals in error bodies, even with debug output on (Phase 07 K-2). */
class NoInternalsTest extends TestCase
{
    public function test_a_server_error_hides_internals_and_logs_them_with_the_request_id(): void
    {
        config(['app.debug' => true]);
        Log::spy();
        Route::middleware(['api', 'access.public'])
            ->get('api/v1/_test/boom', fn () => throw new RuntimeException('SELECT * FROM secret'));

        $response = $this->getJson('/api/v1/_test/boom');

        $response->assertStatus(500)->assertJsonPath('code', 'server_error');
        foreach (['exception', 'file', 'line', 'trace', 'message'] as $key) {
            $response->assertJsonMissingPath($key);
        }
        foreach (['SELECT', 'RuntimeException', '.php'] as $leak) {
            $this->assertStringNotContainsString($leak, $response->getContent());
        }

        $id = $response->headers->get('X-Request-Id');
        Log::shouldHaveReceived('log')
            ->withArgs(fn ($level, $message, $context) => $level === 'error'
                && $message === 'api.error' && $context['request_id'] === $id)
            ->once();
    }
}
