<?php

namespace App\Exceptions\Api;

use App\Http\Responses\ApiResponse;
use Exception;

class NotFoundException extends Exception
{
    protected $code = 404;

    public function __construct() {}

    public function render()
    {
        return ApiResponse::fail(
            errorMessage: 'not found error',
            errors: [],
            statusCode: $this->code
        );
    }
}
