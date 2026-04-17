<?php

namespace App\Http\Requests\Wapi;

use Illuminate\Foundation\Http\FormRequest;

class PreviewWapiTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'wapi_template_id' => ['required', 'integer', 'exists:wapi_templates,id'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:4096'],
            'header_variables' => ['nullable', 'array'],
            'header_variables.*' => ['string', 'max:4096'],
        ];
    }
}
