<?php

namespace App\Support\Problems;

use App\Support\Http\RequestId;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renders every /api/v1 error as RFC 9457 problem details (error-format.md, backend-architecture §11).
 * The framework has already run prepareException(): authorization, model-not-found and CSRF
 * errors arrive as HTTP exceptions (research R-02).
 */
final class ProblemRenderer
{
    /** HTTP statuses of framework exceptions → catalogue code. Anything else is server_error. */
    private const STATUS_CODES = [
        400 => 'bad_request',
        401 => 'unauthenticated',
        403 => 'forbidden',
        404 => 'not_found',
        405 => 'method_not_allowed',
        413 => 'payload_too_large',
        415 => 'unsupported_media_type',
        419 => 'csrf_token_mismatch',
        429 => 'rate_limited',
        503 => 'service_unavailable',
    ];

    /** Failed validation rule → field error code (error-format §3). Anything else is invalid. */
    private const FIELD_CODES = [
        'Required' => 'required',
        'Unique' => 'taken',
        'Accepted' => 'must_be_accepted',
        'Missing' => 'unknown_parameter',
    ];

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') || $e instanceof HttpResponseException) {
            return null;
        }

        [$code, $detail, $errors, $headers] = $this->map($e);
        $status = Catalogue::status($code);
        $id = RequestId::for($request);

        $body = [
            'type' => rtrim((string) config('app.url'), '/').'/problems/'.str_replace('_', '-', $code),
            'title' => __('problems.titles.'.$code),
            'status' => $status,
            'detail' => $detail ?? __('problems.details.'.$code),
            'instance' => '/'.ltrim($request->path(), '/'),
            'code' => $code,
            'request_id' => $id,
        ];
        // Field errors exist only on validation_failed, so `errors` never appears outside 422.
        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        // request_id is written explicitly: Context is empty when boot fails before the middleware runs.
        Log::log($status >= 500 ? 'error' : 'warning', 'api.error', [
            'request_id' => $id, 'status' => $status, 'code' => $code, 'path' => $request->path(),
        ]);

        return new JsonResponse(
            $body,
            $status,
            ['Content-Type' => 'application/problem+json', RequestId::HEADER => $id] + $headers,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }

    /** @return array{0: string, 1: ?string, 2: array, 3: array} code, detail, errors, headers */
    private function map(Throwable $e): array
    {
        return match (true) {
            $e instanceof ApiProblem => [$e->problemCode(), $e->detail(), $e->errors(), $e->getHeaders()],
            $e instanceof ValidationException => ['validation_failed', null, $this->fieldErrors($e->validator), []],
            $e instanceof AuthenticationException => ['unauthenticated', null, [], []],
            $e instanceof InvalidSignatureException => ['invalid_signature', null, [], []],
            $e instanceof HttpExceptionInterface && isset(self::STATUS_CODES[$e->getStatusCode()]) => [self::STATUS_CODES[$e->getStatusCode()], null, [], $e->getHeaders()],
            default => ['server_error', null, [], []],
        };
    }

    private function fieldErrors(Validator $validator): array
    {
        $failed = $validator->failed();
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            $rules = array_keys($failed[$field] ?? []);
            foreach ($messages as $i => $message) {
                $rule = $rules[$i] ?? '';
                $code = self::FIELD_CODES[$rule] ?? 'invalid';
                // A message set by the Form Request wins; otherwise the French text for the code.
                $custom = $validator->customMessages[$field.'.'.Str::snake($rule)] ?? null;
                $errors[$field][] = [
                    'code' => $code,
                    'message' => $custom ?? (Lang::has('problems.fields.'.$code) ? __('problems.fields.'.$code) : $message),
                ];
            }
        }

        return $errors;
    }
}
