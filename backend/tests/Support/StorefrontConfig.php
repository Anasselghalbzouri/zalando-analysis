<?php

namespace Tests\Support;

/** A complete storefront config for tests. Test values only, not PO decisions. */
trait StorefrontConfig
{
    protected function completeStorefrontConfig(): array
    {
        return [
            'reservation_minutes' => 20,
            'reservation_timer_mode' => 'per_item',
            'max_quantity_per_article' => 1,
            'low_stock_threshold' => 3,
            'return_period_days' => 30,
            'packaging_fee_cents' => 390,
            'display_timezone' => 'Europe/Paris',
            'upcoming_relative_threshold_minutes' => 120,
            'upcoming_horizon_hours' => 24,
            'last_hours_badge_threshold_hours' => 24,
            'listing_page_size' => 60,
            'catalog_access' => 'members',
            'features' => ['hide_add_button_after_add' => false, 'last_hours_badge' => false],
        ];
    }

    protected function useStorefrontConfig(array $overrides = []): void
    {
        config(['storefront' => array_replace_recursive($this->completeStorefrontConfig(), $overrides)]);
    }

    /** The 12 policy values (system-overview §7). */
    protected static function policyKeys(): array
    {
        return [
            'reservation_minutes', 'reservation_timer_mode', 'max_quantity_per_article', 'low_stock_threshold',
            'return_period_days', 'packaging_fee_cents', 'display_timezone', 'upcoming_relative_threshold_minutes',
            'upcoming_horizon_hours', 'last_hours_badge_threshold_hours', 'listing_page_size', 'catalog_access',
        ];
    }

    /** One invalid value per policy value (FR-017). */
    protected static function invalidValues(): array
    {
        return [
            'reservation_minutes' => [0, -1, 'abc', '1.5'],
            'reservation_timer_mode' => ['both'],
            'max_quantity_per_article' => [0],
            'low_stock_threshold' => [-1],
            'return_period_days' => ['x'],
            'packaging_fee_cents' => [-1],
            'display_timezone' => ['Paris', 'GMT+1'],
            'upcoming_relative_threshold_minutes' => [-1],
            'upcoming_horizon_hours' => ['x'],
            'last_hours_badge_threshold_hours' => [-1],
            'listing_page_size' => [0],
            'catalog_access' => ['everyone'],
        ];
    }
}
