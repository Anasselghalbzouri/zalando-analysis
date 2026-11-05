<?php

namespace Tests\Unit\Http;

use App\Support\Http\RequestId;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestIdTest extends TestCase
{
    private function request(?string $id): Request
    {
        $request = Request::create('/api/v1/x');
        if ($id !== null) {
            $request->headers->set('X-Request-Id', $id);
        }

        return $request;
    }

    public static function accepted(): array
    {
        return [['qa-123'], [str_repeat('a', 128)], ['00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01'], ['a.b_c']];
    }

    #[DataProvider('accepted')]
    public function test_a_well_formed_inbound_id_is_reused(string $id): void
    {
        $this->assertSame($id, RequestId::for($this->request($id)));
    }

    public static function rejected(): array
    {
        return [[null], [''], [str_repeat('a', 129)], ['bad id<script>'], ["qa\n123"], ["qa-123\n"]];
    }

    #[DataProvider('rejected')]
    public function test_a_missing_or_malformed_inbound_id_is_replaced_by_a_uuid(?string $id): void
    {
        $generated = RequestId::for($this->request($id));

        $this->assertTrue(Str::isUuid($generated));
        $this->assertNotSame($id, $generated);
    }

    public function test_the_id_is_stable_for_one_request(): void
    {
        $request = $this->request(null);

        $this->assertSame(RequestId::for($request), RequestId::for($request));
        $this->assertSame(RequestId::for($request), $request->attributes->get('request_id'));
    }
}
