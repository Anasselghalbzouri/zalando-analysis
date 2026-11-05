<?php

namespace App\Support\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** The correlation ID of one request (NFR-OBS-01, research R-05). */
final class RequestId
{
    public const HEADER = 'X-Request-Id';

    public static function for(Request $request): string
    {
        if ($id = $request->attributes->get('request_id')) {
            return $id;
        }

        // Inbound values are untrusted: reuse only a bounded, log-safe value.
        $inbound = (string) $request->headers->get(self::HEADER, '');
        $id = preg_match('/^[A-Za-z0-9._-]{1,128}$/D', $inbound) ? $inbound : (string) Str::uuid();
        $request->attributes->set('request_id', $id);

        return $id;
    }
}
