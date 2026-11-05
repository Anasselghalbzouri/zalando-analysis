<?php

namespace Tests\Feature\Api;

use App\Modules\Storefront\Exceptions\SettingsIncomplete;
use App\Modules\Storefront\StorefrontServiceProvider;
use Illuminate\Support\Facades\Log;
use Tests\Support\StorefrontConfig;
use Tests\TestCase;

/** Research R-01, backend-architecture §12: production refuses to start with a policy value unset. */
class StorefrontStartupCheckTest extends TestCase
{
    use StorefrontConfig;

    protected function tearDown(): void
    {
        $this->app['env'] = 'testing';
        parent::tearDown();
    }

    private function boot(): void
    {
        (new StorefrontServiceProvider($this->app))->boot();
    }

    public function test_production_with_an_unset_value_refuses_to_start(): void
    {
        $this->app['env'] = 'production';
        $this->useStorefrontConfig(['reservation_minutes' => null]);
        Log::spy();

        try {
            $this->boot();
            $this->fail('SettingsIncomplete was not thrown.');
        } catch (SettingsIncomplete $e) {
            $this->assertSame(['reservation_minutes'], $e->missing);
        }

        Log::shouldHaveReceived('critical')
            ->withArgs(fn ($message, $context) => $context['missing'] === ['reservation_minutes'])
            ->once();
    }

    public function test_production_with_complete_settings_starts(): void
    {
        $this->app['env'] = 'production';
        $this->useStorefrontConfig();

        $this->boot();
        $this->addToAssertionCount(1);
    }

    public function test_other_environments_start_with_incomplete_settings(): void
    {
        $this->useStorefrontConfig(['reservation_minutes' => null]);

        $this->boot();
        $this->addToAssertionCount(1);
    }
}
