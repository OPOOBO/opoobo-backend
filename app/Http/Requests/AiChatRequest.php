<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AiChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant,system'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'messages.required' => 'At least one chat message is required.',
            'messages.*.role.in' => 'Message role must be user, assistant, or system.',
            'messages.*.content.required' => 'Message content is required.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}
