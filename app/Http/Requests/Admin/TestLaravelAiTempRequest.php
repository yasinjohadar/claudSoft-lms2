<?php

namespace App\Http\Requests\Admin;

use App\Models\LaravelAiModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestLaravelAiTempRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::in(LaravelAiModel::allowedProviders())],
            'model' => ['required', 'string', 'max:255'],
            'api_key' => ['required', 'string'],
            'base_url' => ['nullable', 'string', 'max:500'],
        ];
    }
}
