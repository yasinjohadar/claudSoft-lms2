<?php

namespace App\Http\Requests\Wapi;

use App\Models\WapiTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SendWapiCampaignRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'template_id' => ['required', 'string', 'max:128'],
            'group_id' => ['required', 'string', 'max:128'],
            'wapi_template_id' => ['nullable', 'integer', 'exists:wapi_templates,id'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'max:4096'],
            'header_variables' => ['nullable', 'array'],
            'header_variables.*' => ['string', 'max:4096'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
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
