<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class ApiResponse implements Responsable
{
    public function __construct(
        protected readonly array $data = [],
        protected readonly int $statusCode = 200,
        protected readonly string $status = 'success',
        protected readonly string $errorMessage = '',
        protected readonly array $errors = [],
    ) {}

    public function toResponse($request): JsonResponse
    {
        $responseData = ['status' => $this->status];

        $responseData = match ($this->status) {
            'fail' => ['message' => $this->errorMessage, 'errors' => $this->errors],
            'success' => ['data' => $this->data],
            default => throw new InvalidArgumentException("Api response status value must be one of ['succes', 'fail']")
        };

        $responseData = array_merge(['status' => $this->status], $responseData);

        return response()->json($responseData, $this->statusCode);
    }

    public static function success(array $data, int $statusCode = 200): self
    {
        return new self(status: 'success', data: $data, statusCode: $statusCode);
    }

    public static function fail(string $errorMessage, $errors = [], int $statusCode = 400): self
    {
        return new self(status: 'fail', errorMessage: $errorMessage, errors: $errors, statusCode: $statusCode);
    }
}
