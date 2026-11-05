<?php

namespace App\Modules\Storefront\Exceptions;

use App\Support\Problems\ApiProblem;

/** A policy value is unset or invalid: fail closed with 503. The names go to the log, never the body. */
final class SettingsIncomplete extends ApiProblem
{
    /** @param list<string> $missing */
    public function __construct(public readonly array $missing)
    {
        parent::__construct('settings_incomplete');
    }
}
