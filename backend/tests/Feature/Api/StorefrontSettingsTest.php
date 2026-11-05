<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StorefrontConfig;
use Tests\TestCase;

/** Spec US2, story AC-4, FR-018–FR-020, SC-004. */
class StorefrontSettingsTest extends TestCase
{
    use StorefrontConfig;

    private const URI = '/api/v1/storefront/settings';

    public function test_complete_settings_are_served_in_the_contract_shape(): void
    {
        $this->useStorefrontConfig(['reservation_minutes' => '20']); // env values arrive as strings

        $response = $this->getJson(self::URI)->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame([
            'reservation_minutes' => 20,
            'reservation_timer_mode' => 'per_item',
            'max_quantity_per_article' => 1,
            'low_stock_threshold' => 3,
            'return_period_days' => 30,
            'packaging_fee' => ['amount_cents' => 390, 'currency' => 'EUR'],
            'display_timezone' => 'Europe/Paris',
            'upcoming_relative_threshold_minutes' => 120,
            'listing_page_size' => 60,
            'catalog_access' => 'members',
            'features' => [
                'hide_add_button_after_add' => false,
                'last_hours_badge' => false,
                'last_hours_badge_threshold_hours' => 24,
            ],
        ], $response->json('data'));
    }

    public static function failClosed(): array
    {
        $cases = [];
        foreach (array_diff(self::policyKeys(), ['listing_page_size']) as $key) {
            $cases["$key unset"] = [$key, null];
        }
        foreach (self::invalidValues() as $key => $values) {
            $cases["$key invalid"] = [$key, $values[0]];
        }

        return $cases;
    }

    #[DataProvider('failClosed')]
    public function test_an_unset_or_invalid_value_fails_closed(string $key, mixed $value): void
    {
        $this->useStorefrontConfig();
        config(["storefront.$key" => $value]);
        Log::spy();

        $response = $this->getJson(self::URI);

        $response->assertStatus(503)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'settings_incomplete')
            ->assertJsonMissingPath('data');
        $this->assertStringNotContainsString($key, $response->getContent()); // names only in the log
        Log::shouldHaveReceived('critical')
            ->withArgs(fn ($message, $context) => $message === 'storefront.settings_incomplete'
                && in_array($key, $context['missing'], true))
            ->once();
    }

    public function test_an_unknown_parameter_is_rejected(): void
    {
        $this->useStorefrontConfig();

        $this->getJson(self::URI.'?foo=1')->assertStatus(422)->assertJsonPath('errors.foo.0.code', 'unknown_parameter');
    }

    public function test_the_route_is_public(): void
    {
        $route = Route::getRoutes()->getByName('storefront.settings');

        $this->assertSame('api/v1/storefront/settings', $route->uri());
        $this->assertContains('access.public', $route->gatherMiddleware());
    }
}
