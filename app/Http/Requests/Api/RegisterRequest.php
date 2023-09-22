<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
class RegisterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => 'required|string|between:2,100|regex:/^\S*$/|unique:users',
            // 'email' => 'required|string|email|max:100|unique:users',
            'email' => 'required|max:320|min:5|email:rfc,dns,filter|unique:users',

            'password' => 'required|string|confirmed|min:6',
        ];
    }

    public function messages()
    {
        return [
            'username.regex' => 'The username must not contain spaces.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = ApiResponse::unprocessableEntity('Validation errors', $validator->errors());
        throw new ValidationException($validator, $response);
    }
}
