<?php

namespace App\Http\Requests\Wapi;

use App\Models\WapiTemplate;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendWapiTemplateRequest extends FormRequest
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
        $maxKb = (int) config('services.whatsapp.max_attachment_kb', 5120);

        return [
            'phone' => ['required', 'string', 'max:40'],
            'wapi_template_id' => ['nullable', 'integer', 'exists:wapi_templates,id'],
            'template_name' => ['required_without:wapi_template_id', 'string', 'max:255'],
            'language' => ['required_without:wapi_template_id', 'string', 'max:24'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:4096'],
            'header_variables' => ['nullable', 'array'],
            'header_variables.*' => ['string', 'max:4096'],
            'components' => ['nullable', 'array'],
            'components.*' => ['string', 'max:8192'],
            'attachment' => [
                'nullable',
                'file',
                'max:'.$maxKb,
                'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('wapi_template_id')) {
            $t = WapiTemplate::query()->find($this->input('wapi_template_id'));
            if ($t) {
                $this->merge([
                    'template_name' => $this->input('template_name') ?? $t->name,
                    'language' => $this->input('language') ?? $t->language,
                ]);
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $phone = $this->input('phone');
            if (! is_string($phone)) {
                return;
            }

            $normalized = WapiPhoneNormalizer::normalize($phone);
            if (! WapiPhoneNormalizer::isValidE164Digits($normalized)) {
                $validator->errors()->add('phone', __('رقم الهاتف غير صالح (تنسيق دولي).'));
            }

            $tid = $this->input('wapi_template_id');
            if ($tid === null) {
                return;
            }

            $template = WapiTemplate::query()->find($tid);
            if (! $template || ! is_array($template->structure)) {
                return;
            }

            $structure = $template->structure;
            $headerNeed = (int) ($structure['header_placeholders'] ?? 0);
            $bodyNeed = (int) ($structure['body_placeholders'] ?? 0);

            $headerVars = $this->input('header_variables', []);
            $bodyVars = $this->input('variables', []);
            if (! is_array($headerVars)) {
                $headerVars = [];
            }
            if (! is_array($bodyVars)) {
                $bodyVars = [];
            }

            if ($headerNeed > 0 && count($headerVars) < $headerNeed) {
                $validator->errors()->add('header_variables', __('متغيرات الرأس غير مكتملة لهذا القالب.'));
            }
            if ($bodyNeed > 0 && count($bodyVars) < $bodyNeed) {
                $validator->errors()->add('variables', __('متغيرات النص غير مكتملة لهذا القالب.'));
            }
        });
    }
}
