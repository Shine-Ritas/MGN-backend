<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublishingRequest extends FormRequest
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
            'mogou_slug' => 'nullable|string|exists:mogous,slug',
            'sub_mogou_slug' => 'nullable|string',
            'type' => 'required|string|in:mogou,sub_mogou',
            'text_content' => 'nullable|string',
            'social_channel_ids' => ['required', function ($attribute, $value, $fail) {
                if (!is_array($value) && $value !== 'all') {
                    $fail('The social_channel_ids must be either an array of IDs or the string "all".');
                }
            }],
            'social_channel_ids.*' => 'required_if:social_channel_ids,array|integer|exists:social_channels,id',
        ];
    }
}
