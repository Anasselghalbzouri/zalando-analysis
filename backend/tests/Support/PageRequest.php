<?php

namespace Tests\Support;

use App\Support\Http\ApiFormRequest;

/** Test-only Form Request declaring nested query parameters. */
class PageRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return ['page' => 'array', 'page.number' => 'integer|min:1', 'page.size' => 'integer|min:1'];
    }
}
