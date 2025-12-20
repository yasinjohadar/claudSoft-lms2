<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class N8nIncomingHandlerRequest extends FormRequest
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
        return [
            'handler_type' => ['required', 'string', 'max:100'],
            'handler_class' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'required_fields' => ['nullable', 'array'],
            'optional_fields' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'example_payload' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'handler_type' => 'نوع المعالج',
            'handler_class' => 'فئة المعالج',
            'description' => 'الوصف',
            'required_fields' => 'الحقول المطلوبة',
            'optional_fields' => 'الحقول الاختيارية',
            'is_active' => 'الحالة',
            'example_payload' => 'مثال البيانات',
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'handler_type.required' => 'نوع المعالج مطلوب',
            'handler_class.required' => 'فئة المعالج مطلوبة',
        ];
    }
}
