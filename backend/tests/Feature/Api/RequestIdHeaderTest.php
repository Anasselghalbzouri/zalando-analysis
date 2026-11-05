<?php

namespace Tests\Feature\Api;

use App\Support\Http\ApiFormRequest;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Story AC-2, AC-NFR-OBS-01-1, FR-010, FR-011. */
class RequestIdHeaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'access.public'])
            ->get('api/v1/_test/ok', fn (ApiFormRequest $request) => ['data' => ['context_id' => Context::get('request_id')]]);
    }

    public function test_a_well_formed_inbound_id_is_echoed_on_success_and_shared_with_the_log_context(): void
    {
        $this->getJson('/api/v1/_test/ok', ['X-Request-Id' => 'qa-123'])
            ->assertOk()
            ->assertHeader('X-Request-Id', 'qa-123')
            ->assertJsonPath('data.context_id', 'qa-123');
    }

    public function test_an_id_is_generated_when_none_is_sent(): void
    {
        $this->assertTrue(Str::isUuid($this->getJson('/api/v1/_test/ok')->headers->get('X-Request-Id')));
    }

    public function test_a_hostile_inbound_id_is_replaced(): void
    {
        $id = $this->getJson('/api/v1/_test/ok', ['X-Request-Id' => 'bad id<script>'])->headers->get('X-Request-Id');

        $this->assertTrue(Str::isUuid($id));
    }

    public function test_a_failed_call_can_be_correlated_with_its_log_entry(): void
    {
        Log::spy();

        $this->getJson('/api/v1/nope?email=someone@example.test', ['X-Request-Id' => 'qa-404'])
            ->assertStatus(404)
            ->assertHeader('X-Request-Id', 'qa-404')
            ->assertJsonPath('request_id', 'qa-404');

        Log::shouldHaveReceived('log')->withArgs(fn ($level, $message, $context) => $level === 'warning'
            && $message === 'api.error'
            && $context === ['request_id' => 'qa-404', 'status' => 404, 'code' => 'not_found', 'path' => 'api/v1/nope'])
            ->once();
    }
}
