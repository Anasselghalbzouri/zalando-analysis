<?php

namespace App\Support\Http;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Base Form Request for every /api/v1 action (research R-04). The rule keys declare the
 * accepted query parameters; any other key fails with field code unknown_parameter (422).
 * An action without parameters type-hints this class directly. Subclasses override rules(),
 * never validationRules(), so the check can't be dropped by accident.
 */
class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function validationRules()
    {
        $rules = parent::validationRules();
        $declared = array_keys($rules);

        foreach (array_keys(Arr::dot($this->query())) as $key) {
            // A leaf key must match a rule key (wildcards allowed); a parent rule doesn't admit children.
            // `missing` is presence-based and implicit, so `?foo=` and a bare `?foo` fail too.
            if (! Str::is($declared, (string) $key)) {
                $rules[$key] = ['missing'];
            }
        }

        return $rules;
    }
}
