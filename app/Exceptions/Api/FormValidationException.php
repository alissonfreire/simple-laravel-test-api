<?php

namespace App\Exceptions\Api;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Contracts\Validation\Validator;

class FormValidationException extends Exception
{
    protected $code = 422;

    public function __construct(private Validator $validator) {}

    public function render()
    {
        return ApiResponse::fail(
            errorMessage: 'form validation error',
            errors: $this->validator->errors()->toArray(),
            statusCode: $this->code
        );
    }
}
