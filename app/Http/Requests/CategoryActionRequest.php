<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'is_adult' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'is_adult.required' => 'The adult category field is required.',
            'is_adult.boolean' => 'The adult category field must be a boolean.',
        ];
    }
}
