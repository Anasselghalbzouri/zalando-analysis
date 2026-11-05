<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * One "now" per request (ADR-009), reused by every time-based rule (US-015 T-015-B2, US-053).
 * Stored on the request, not in a scoped binding: the HTTP kernel never resets scoped instances (research R-06).
 */
final class RequestClock
{
    public static function now(?Request $request = null): CarbonImmutable
    {
        $request ??= request();

        if (! $request->attributes->has('request_now')) {
            $request->attributes->set('request_now', CarbonImmutable::now('UTC'));
        }

        return $request->attributes->get('request_now');
    }
}
