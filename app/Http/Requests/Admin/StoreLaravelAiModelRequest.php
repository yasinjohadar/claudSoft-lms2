<?php

namespace App\Http\Requests\Admin;

use App\Models\LaravelAiModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLaravelAiModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', Rule::in(LaravelAiModel::allowedProviders())],
            'model' => ['required', 'string', 'max:255'],
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'max:100'],
            'max_tokens' => ['required', 'integer', 'min:1', 'max:200000'],
            'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
        ];
    }
}
