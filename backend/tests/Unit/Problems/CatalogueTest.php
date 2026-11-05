<?php

namespace Tests\Unit\Problems;

use App\Support\Problems\Catalogue;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CatalogueTest extends TestCase
{
    /** error-format §2 plus erratum E-01. */
    public static function codes(): array
    {
        return [
            ['bad_request', 400], ['unauthenticated', 401], ['forbidden', 403], ['invalid_signature', 403],
            ['not_found', 404], ['method_not_allowed', 405], ['out_of_stock', 409],
            ['reservation_limit_reached', 409], ['campaign_not_live', 409], ['campaign_ended', 410],
            ['payload_too_large', 413], ['unsupported_media_type', 415], ['csrf_token_mismatch', 419],
            ['validation_failed', 422], ['invalid_credentials', 422], ['invalid_token', 422],
            ['rate_limited', 429], ['server_error', 500], ['settings_incomplete', 503],
            ['service_unavailable', 503],
        ];
    }

    #[DataProvider('codes')]
    public function test_each_code_has_its_catalogue_status(string $code, int $status): void
    {
        $this->assertSame($status, Catalogue::status($code));
    }

    public function test_the_catalogue_has_exactly_twenty_codes(): void
    {
        $this->assertCount(20, Catalogue::CODES);
    }

    public function test_an_unknown_code_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Catalogue::status('nope');
    }
}
