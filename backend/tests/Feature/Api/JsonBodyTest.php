<?php

namespace Tests\Feature\Api;

use App\Support\Http\ApiFormRequest;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Research R-07: malformed JSON is 400, a non-JSON body is 415 (erratum E-01). */
class JsonBodyTest extends TestCase
{
    private const URI = '/api/v1/_test/echo';

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['api', 'access.public'])->post('api/v1/_test/echo', fn (ApiFormRequest $request) => ['data' => []]);
    }

    private function send(string $contentType, string $body)
    {
        $server = ['HTTP_ACCEPT' => 'application/json'] + ($contentType !== '' ? ['CONTENT_TYPE' => $contentType] : []);

        return $this->call('POST', self::URI, [], [], [], $server, $body);
    }

    public function test_malformed_json_is_bad_request(): void
    {
        $this->send('application/json', '{')->assertStatus(400)->assertJsonPath('code', 'bad_request');
    }

    public function test_a_non_json_body_is_unsupported_media_type(): void
    {
        $this->send('text/plain', 'x')->assertStatus(415)->assertJsonPath('code', 'unsupported_media_type');
    }

    public function test_an_empty_body_passes(): void
    {
        $this->send('', '')->assertOk();
    }

    public function test_valid_json_passes(): void
    {
        $this->send('application/json', '{}')->assertOk();
    }
}
