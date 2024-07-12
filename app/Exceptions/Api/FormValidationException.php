<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class FormValidationException extends Exception
{
    protected $code = 422;

    public function __construct(private Validator $validator) {}

    public function render()
    {
        return response()->json([
            'status' => 'fail',
            'errors' => $this->validator->errors()->toArray(),
            'message' => 'form validation error',
        ], $this->code);
    }
}
