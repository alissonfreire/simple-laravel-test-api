<?php

namespace App\Http\Requests\Api;

use App\Exceptions\Api\FormValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \App\Exceptions\Api\FormValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new FormValidationException($validator);
    }
}
