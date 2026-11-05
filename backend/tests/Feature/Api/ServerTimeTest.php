<?php

namespace Tests\Feature\Api;

use App\Support\Http\ApiFormRequest;
use Illuminate\Support\Facades\Route;
use Tests\Support\StorefrontConfig;
use Tests\TestCase;

/** Spec US3, story AC-3, FR-013, FR-014. */
class ServerTimeTest extends TestCase
{
    use StorefrontConfig;

    protected function setUp(): void
    {
        parent::setUp();

        Route::prefix('api/v1/_test')->middleware(['api', 'access.public'])->group(function () {
            Route::get('meta', fn (ApiFormRequest $r) => ['data' => [], 'meta' => ['campaign_count' => 2]]);
            Route::get('no-content', fn (ApiFormRequest $r) => response()->noContent());
            Route::get('no-envelope', fn (ApiFormRequest $r) => ['foo' => 'bar']);
        });
        $this->travelTo('2026-11-05T12:00:00Z');
    }

    public function test_the_settings_carry_the_frozen_server_time(): void
    {
        $this->useStorefrontConfig();

        $this->getJson('/api/v1/storefront/settings')
            ->assertOk()
            ->assertJsonPath('meta.server_time', '2026-11-05T12:00:00Z')
            ->assertJsonPath('data.listing_page_size', 60);
    }

    public function test_existing_meta_keys_are_kept(): void
    {
        $this->getJson('/api/v1/_test/meta')
            ->assertJsonPath('meta.campaign_count', 2)
            ->assertJsonPath('meta.server_time', '2026-11-05T12:00:00Z');
    }

    public function test_responses_without_a_body_or_envelope_are_untouched(): void
    {
        $this->assertSame('', $this->get('/api/v1/_test/no-content')->assertNoContent()->getContent());
        $this->getJson('/api/v1/_test/no-envelope')->assertExactJson(['foo' => 'bar']);
    }

    public function test_error_responses_have_no_meta(): void
    {
        $this->getJson('/api/v1/_test/meta?foo=1')->assertStatus(422)->assertJsonMissingPath('meta');
    }

    public function test_each_request_reads_its_own_now(): void
    {
        $first = $this->getJson('/api/v1/_test/meta')->json('meta.server_time');
        $this->travel(1)->minutes();
        $second = $this->getJson('/api/v1/_test/meta')->json('meta.server_time');

        $this->assertSame('2026-11-05T12:00:00Z', $first);
        $this->assertSame('2026-11-05T12:01:00Z', $second);
    }
}
