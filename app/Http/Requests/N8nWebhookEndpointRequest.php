<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class N8nWebhookEndpointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super-admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $endpointId = $this->route('n8n_webhook') ?? $this->route('endpoint');

        return [
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'retry_attempts' => ['integer', 'min:1', 'max:10'],
            'timeout' => ['integer', 'min:5', 'max:120'],
            'headers' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم نقطة النهاية',
            'event_type' => 'نوع الحدث',
            'url' => 'رابط الـ Webhook',
            'secret_key' => 'المفتاح السري',
            'is_active' => 'الحالة',
            'retry_attempts' => 'عدد المحاولات',
            'timeout' => 'المهلة الزمنية',
            'headers' => 'الرؤوس',
            'metadata' => 'البيانات الوصفية',
            'description' => 'الوصف',
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم نقطة النهاية مطلوب',
            'event_type.required' => 'نوع الحدث مطلوب',
            'url.required' => 'رابط الـ Webhook مطلوب',
            'url.url' => 'يجب أن يكون الرابط صحيحاً',
            'retry_attempts.min' => 'الحد الأدنى لعدد المحاولات هو 1',
            'retry_attempts.max' => 'الحد الأقصى لعدد المحاولات هو 10',
            'timeout.min' => 'الحد الأدنى للمهلة الزمنية هو 5 ثواني',
            'timeout.max' => 'الحد الأقصى للمهلة الزمنية هو 120 ثانية',
        ];
    }
}
