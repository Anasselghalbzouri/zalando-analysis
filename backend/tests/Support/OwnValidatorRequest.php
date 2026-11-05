<?php

namespace Tests\Support;

use App\Support\Http\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;

/** Test-only: a subclass with its own withValidator() keeps the unknown-parameter check. */
class OwnValidatorRequest extends ApiFormRequest
{
    public function withValidator(Validator $validator): void
    {
        //
    }
}
