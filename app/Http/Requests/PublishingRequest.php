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
        return false;
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
            'sub_mogou_slug' => 'nullable|string|exists:sub_mogous,slug',
            'type' => 'required|string|in:mogou,sub_mogou',
            'text_content' => 'nullable|string',
            'social_channel_ids' => 'required|array',
            'social_channel_ids.*' => 'required|integer|exists:social_channels,id',
        ];
    }
}
