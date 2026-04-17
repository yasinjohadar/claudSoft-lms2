<?php

namespace App\Http\Requests\Wapi;

use App\Support\WapiPhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class SendWapiMessageRequest extends FormRequest
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
            'message' => ['nullable', 'string', 'max:4096'],
            'header' => ['nullable', 'string', 'max:1024'],
            'footer' => ['nullable', 'string', 'max:1024'],
            'buttons' => ['nullable', 'string', 'max:2048'],
            'attachment' => [
                'nullable',
                'file',
                'max:'.$maxKb,
                'mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $phone = $this->input('phone');
            if (! is_string($phone)) {
                return;
            }

            $normalized = WapiPhoneNormalizer::normalize($phone);
            if (! WapiPhoneNormalizer::isValidE164Digits($normalized)) {
                $validator->errors()->add('phone', __('رقم الهاتف غير صالح (استخدم تنسيقاً دولياً مثل 9665xxxxxxxx).'));

                return;
            }

            if (! $this->hasFile('attachment')) {
                $msg = $this->input('message');
                if (! is_string($msg) || trim($msg) === '') {
                    $validator->errors()->add('message', __('الرسالة أو المرفق مطلوب.'));
                }
            }
        });
    }
}
