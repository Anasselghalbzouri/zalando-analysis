<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Support\Problems\ApiProblem;
use App\Support\Problems\Catalogue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Tests\TestCase;

/** T-059-BT1: every catalogue code renders with its status and the problem shape (story AC-1). */
class ProblemRendererTest extends TestCase
{
    private const KEYS = ['type', 'title', 'status', 'detail', 'instance', 'code', 'request_id'];

    protected function setUp(): void
    {
        parent::setUp();

        Route::prefix('api/v1/_test')->middleware(['api', 'access.public'])->group(function () {
            Route::get('raise/{case}', fn (string $case) => throw self::exceptionFor($case));
        });
    }

    private static function exceptionFor(string $case): \Throwable
    {
        return match ($case) {
            'bad_request' => new BadRequestHttpException,
            'unauthenticated' => new AuthenticationException,
            'forbidden' => new AuthorizationException,
            'invalid_signature' => new InvalidSignatureException,
            'not_found' => (new ModelNotFoundException)->setModel(User::class),
            'payload_too_large' => new PostTooLargeException,
            'unsupported_media_type' => new UnsupportedMediaTypeHttpException,
            'csrf_token_mismatch' => new TokenMismatchException,
            'validation_failed' => ValidationException::withMessages(['email' => 'x']),
            'rate_limited' => new ThrottleRequestsException(headers: ['Retry-After' => 30]),
            'service_unavailable' => new HttpException(503),
            'server_error' => new RuntimeException('SELECT * FROM secret'),
            default => new ApiProblem($case),
        };
    }

    public static function cases(): array
    {
        $cases = array_keys(Catalogue::CODES);
        $cases = array_values(array_diff($cases, ['method_not_allowed']));

        return array_combine($cases, array_map(fn ($c) => [$c], $cases));
    }

    #[DataProvider('cases')]
    public function test_each_catalogue_code_renders_as_a_problem(string $case): void
    {
        $response = $this->getJson("/api/v1/_test/raise/$case");

        $this->assertProblem($response, $case, "/api/v1/_test/raise/$case");
    }

    public function test_a_wrong_method_is_405_with_allow(): void
    {
        $response = $this->deleteJson('/api/v1/_test/raise/x');

        $this->assertProblem($response, 'method_not_allowed', '/api/v1/_test/raise/x');
        $this->assertStringContainsString('GET', $response->headers->get('Allow'));
    }

    public function test_rate_limited_keeps_retry_after(): void
    {
        $this->getJson('/api/v1/_test/raise/rate_limited')->assertHeader('Retry-After', '30');
    }

    public function test_validation_failed_lists_field_errors(): void
    {
        $this->getJson('/api/v1/_test/raise/validation_failed')
            ->assertJsonPath('errors.email.0.code', 'invalid')
            ->assertJsonPath('errors.email.0.message', 'La valeur est invalide.');
    }

    public function test_an_unknown_api_route_is_not_found(): void
    {
        $this->assertProblem($this->getJson('/api/v1/does-not-exist'), 'not_found', '/api/v1/does-not-exist');
    }

    private function assertProblem($response, string $code, string $instance): void
    {
        $response->assertStatus(Catalogue::status($code));
        $this->assertStringStartsWith('application/problem+json', $response->headers->get('Content-Type'));

        $body = $response->json();
        $expected = $code === 'validation_failed' ? [...self::KEYS, 'errors'] : self::KEYS;
        $this->assertEqualsCanonicalizing($expected, array_keys($body));
        $this->assertSame($code, $body['code']);
        $this->assertSame(Catalogue::status($code), $body['status']);
        $this->assertStringEndsWith('/problems/'.str_replace('_', '-', $code), $body['type']);
        $this->assertSame($instance, $body['instance']);
        $this->assertSame($response->headers->get('X-Request-Id'), $body['request_id']);
        $this->assertNotEmpty($body['title']);
        $this->assertNotEmpty($body['detail']);
    }
}
