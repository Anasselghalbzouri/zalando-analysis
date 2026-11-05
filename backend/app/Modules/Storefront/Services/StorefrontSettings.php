<?php

namespace App\Modules\Storefront\Services;

use App\Modules\Storefront\Exceptions\SettingsIncomplete;
use DateTimeZone;
use Illuminate\Support\Facades\Log;

/** The storefront policy values: validated, never defaulted (system-overview §7, Principle VII). */
final class StorefrontSettings
{
    /** Integer settings and their minimum (FR-017). */
    private const INTEGERS = [
        'reservation_minutes' => 1,
        'max_quantity_per_article' => 1,
        'low_stock_threshold' => 0,
        'return_period_days' => 0,
        'packaging_fee_cents' => 0,
        'upcoming_relative_threshold_minutes' => 0,
        'upcoming_horizon_hours' => 0,
        'last_hours_badge_threshold_hours' => 0,
        'listing_page_size' => 1,
    ];

    private const CHOICES = [
        'reservation_timer_mode' => ['per_item', 'shared'],
        'catalog_access' => ['members', 'public'],
    ];

    /** @return list<string> the names of unset or invalid settings */
    public function missing(): array
    {
        $missing = [];

        foreach (self::INTEGERS as $key => $min) {
            if ($this->int($key, $min) === null) {
                $missing[] = $key;
            }
        }
        foreach (self::CHOICES as $key => $choices) {
            if (! in_array($this->raw($key), $choices, true)) {
                $missing[] = $key;
            }
        }
        if (! in_array($this->raw('display_timezone'), DateTimeZone::listIdentifiers(), true)) {
            $missing[] = 'display_timezone';
        }

        return $missing;
    }

    public function assertComplete(): void
    {
        if ($missing = $this->missing()) {
            Log::critical('storefront.settings_incomplete', ['missing' => $missing]);

            throw new SettingsIncomplete($missing);
        }
    }

    /** The public settings payload (api-contracts §3). upcoming_horizon_hours is server-only (FR-020). */
    public function toPublicArray(): array
    {
        $this->assertComplete();

        return [
            'reservation_minutes' => $this->int('reservation_minutes'),
            'reservation_timer_mode' => $this->raw('reservation_timer_mode'),
            'max_quantity_per_article' => $this->int('max_quantity_per_article'),
            'low_stock_threshold' => $this->int('low_stock_threshold'),
            'return_period_days' => $this->int('return_period_days'),
            'packaging_fee' => ['amount_cents' => $this->int('packaging_fee_cents'), 'currency' => 'EUR'],
            'display_timezone' => $this->raw('display_timezone'),
            'upcoming_relative_threshold_minutes' => $this->int('upcoming_relative_threshold_minutes'),
            'listing_page_size' => $this->int('listing_page_size'),
            'catalog_access' => $this->raw('catalog_access'),
            'features' => [
                'hide_add_button_after_add' => (bool) config('storefront.features.hide_add_button_after_add'),
                'last_hours_badge' => (bool) config('storefront.features.last_hours_badge'),
                'last_hours_badge_threshold_hours' => $this->int('last_hours_badge_threshold_hours'),
            ],
        ];
    }

    private function raw(string $key): mixed
    {
        return config('storefront.'.$key);
    }

    private function int(string $key, ?int $min = null): ?int
    {
        $value = filter_var($this->raw($key), FILTER_VALIDATE_INT, ['options' => ['min_range' => $min ?? self::INTEGERS[$key]]]);

        return $value === false ? null : $value;
    }
}
