<?php

namespace Tests\Feature\Api;

use App\Support\Http\ApiFormRequest;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\OwnValidatorRequest;
use Tests\Support\PageRequest;
use Tests\TestCase;

class UnknownParameterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::prefix('api/v1/_test')->middleware(['api', 'access.public'])->group(function () {
            Route::get('params-none', fn (ApiFormRequest $request) => ['data' => []]);
            Route::get('params-page', fn (PageRequest $request) => ['data' => []]);
            Route::get('own-validator', fn (OwnValidatorRequest $request) => ['data' => []]);
        });
    }

    public static function unknownFoo(): array
    {
        return [['?foo=1'], ['?foo='], ['?foo']];
    }

    #[DataProvider('unknownFoo')]
    public function test_an_undeclared_parameter_is_rejected_even_when_empty(string $query): void
    {
        $response = $this->getJson('/api/v1/_test/params-none'.$query);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonPath('errors.foo.0.code', 'unknown_parameter');
        $this->assertNotSame('', $response->json('errors.foo.0.message'));
    }

    public function test_a_declared_nested_parameter_is_accepted(): void
    {
        $this->getJson('/api/v1/_test/params-page?page[number]=2')->assertOk();
    }

    public function test_an_undeclared_nested_parameter_is_reported_under_its_dot_path(): void
    {
        $errors = $this->getJson('/api/v1/_test/params-page?page[bogus]=1')->assertStatus(422)->json('errors');

        $this->assertSame('unknown_parameter', $errors['page.bogus'][0]['code']);
    }

    public function test_an_invalid_declared_parameter_has_the_invalid_field_code(): void
    {
        $errors = $this->getJson('/api/v1/_test/params-page?page[number]=x')->assertStatus(422)->json('errors');

        $this->assertSame('invalid', $errors['page.number'][0]['code']);
    }

    public function test_a_subclass_with_its_own_with_validator_keeps_the_check(): void
    {
        $this->getJson('/api/v1/_test/own-validator?foo=1')->assertStatus(422)->assertJsonPath('errors.foo.0.code', 'unknown_parameter');
    }
}
