<?php

namespace App\Support\Problems;

use InvalidArgumentException;

/**
 * The API error code catalogue: error-format.md §2 plus erratum E-01 (405, 413, 415).
 * The only source of the HTTP status of a problem code.
 */
final class Catalogue
{
    public const CODES = [
        'bad_request' => 400,
        'unauthenticated' => 401,
        'forbidden' => 403,
        'invalid_signature' => 403,
        'not_found' => 404,
        'method_not_allowed' => 405,
        'out_of_stock' => 409,
        'reservation_limit_reached' => 409,
        'campaign_not_live' => 409,
        'campaign_ended' => 410,
        'payload_too_large' => 413,
        'unsupported_media_type' => 415,
        'csrf_token_mismatch' => 419,
        'validation_failed' => 422,
        'invalid_credentials' => 422,
        'invalid_token' => 422,
        'rate_limited' => 429,
        'server_error' => 500,
        'settings_incomplete' => 503,
        'service_unavailable' => 503,
    ];

    public static function status(string $code): int
    {
        return self::CODES[$code] ?? throw new InvalidArgumentException("Unknown problem code [$code].");
    }
}
