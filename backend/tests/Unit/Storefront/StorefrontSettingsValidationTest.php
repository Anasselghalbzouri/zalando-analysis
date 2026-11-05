<?php

namespace Tests\Unit\Storefront;

use App\Modules\Storefront\Services\StorefrontSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\StorefrontConfig;
use Tests\TestCase;

/** FR-016, FR-017, FR-021: validation of the policy values. */
class StorefrontSettingsValidationTest extends TestCase
{
    use StorefrontConfig;

    private function missing(): array
    {
        return app(StorefrontSettings::class)->missing();
    }

    public function test_a_complete_config_has_nothing_missing(): void
    {
        $this->useStorefrontConfig();
        $this->assertSame([], $this->missing());
    }

    public static function undecided(): array
    {
        $keys = array_values(array_diff(self::policyKeys(), ['listing_page_size']));

        return array_combine($keys, array_map(fn ($k) => [$k], $keys));
    }

    #[DataProvider('undecided')]
    public function test_an_unset_value_is_missing(string $key): void
    {
        $this->useStorefrontConfig();
        config(["storefront.$key" => null]);

        $this->assertSame([$key], $this->missing());
    }

    public static function invalid(): array
    {
        $cases = [];
        foreach (self::invalidValues() as $key => $values) {
            foreach ($values as $value) {
                $cases["$key = ".var_export($value, true)] = [$key, $value];
            }
        }

        return $cases;
    }

    #[DataProvider('invalid')]
    public function test_an_invalid_value_counts_as_missing(string $key, mixed $value): void
    {
        $this->useStorefrontConfig();
        config(["storefront.$key" => $value]);

        $this->assertSame([$key], $this->missing());
    }

    public function test_numeric_strings_from_the_environment_are_accepted(): void
    {
        $this->useStorefrontConfig(['reservation_minutes' => '20', 'packaging_fee_cents' => '0']);
        $this->assertSame([], $this->missing());
    }

    public function test_only_page_size_and_the_switches_have_defaults_in_the_config_file(): void
    {
        $names = ['STOREFRONT_LISTING_PAGE_SIZE', 'FEATURE_HIDE_ADD_BUTTON_AFTER_ADD', 'FEATURE_LAST_HOURS_BADGE',
            'STOREFRONT_RESERVATION_MINUTES', 'STOREFRONT_CATALOG_ACCESS', 'STOREFRONT_DISPLAY_TIMEZONE'];
        $saved = [];
        foreach ($names as $name) {
            $saved[$name] = [getenv($name), $_ENV[$name] ?? null, $_SERVER[$name] ?? null];
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }

        try {
            $config = require base_path('config/storefront.php');
        } finally {
            foreach ($saved as $name => [$env, $envArr, $server]) {
                if ($env !== false) {
                    putenv("$name=$env");
                }
                if ($envArr !== null) {
                    $_ENV[$name] = $envArr;
                }
                if ($server !== null) {
                    $_SERVER[$name] = $server;
                }
            }
        }

        $this->assertSame(60, $config['listing_page_size']); // CONFIRMED (BR-06-07)
        $this->assertFalse($config['features']['hide_add_button_after_add']);
        $this->assertFalse($config['features']['last_hours_badge']);
        $this->assertNull($config['reservation_minutes']);
        $this->assertNull($config['catalog_access']);
        $this->assertNull($config['display_timezone']);
    }
}
