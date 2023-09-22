<?php

namespace App\Http\Requests\Api;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'required|array',
            'tags.*' => 'string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = ApiResponse::unprocessableEntity('Validation errors', $validator->errors());
        throw new ValidationException($validator, $response);
    }
}
