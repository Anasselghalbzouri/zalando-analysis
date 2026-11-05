<?php

namespace Tests\Unit;

use App\Support\RequestClock;
use Illuminate\Http\Request;
use Tests\TestCase;

/** ADR-009: one "now" per request (research R-06). */
class RequestClockTest extends TestCase
{
    public function test_the_first_reading_is_kept_for_the_whole_request(): void
    {
        $this->travelTo('2026-11-05 12:00:00');
        $request = Request::create('/api/v1/x');

        $first = RequestClock::now($request);
        $this->travel(5)->seconds();

        $this->assertSame('2026-11-05T12:00:00Z', $first->toIso8601ZuluString());
        $this->assertTrue($first->equalTo(RequestClock::now($request)));
        $this->assertSame('UTC', $first->getTimezone()->getName());
    }

    public function test_a_new_request_reads_the_clock_again(): void
    {
        $this->travelTo('2026-11-05 12:00:00');
        RequestClock::now(Request::create('/a'));
        $this->travel(5)->seconds();

        $this->assertSame('2026-11-05T12:00:05Z', RequestClock::now(Request::create('/b'))->toIso8601ZuluString());
    }
}
