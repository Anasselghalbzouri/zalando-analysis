<?php

namespace App\Support\Problems;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * An API error carrying a catalogue code. Its status always comes from the catalogue.
 * Later features raise domain errors with `throw new ApiProblem('out_of_stock')`.
 */
class ApiProblem extends HttpException
{
    public function __construct(
        private readonly string $problemCode,
        private readonly ?string $detail = null,
        private readonly array $errors = [],
        array $headers = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct(Catalogue::status($problemCode), $detail ?? '', $previous, $headers);
    }

    public function problemCode(): string
    {
        return $this->problemCode;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
