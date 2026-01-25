<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // يمكن إضافة middleware للصلاحيات لاحقاً
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'type' => 'required|in:registration_welcome,enrollment_confirmation,custom',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم القالب مطلوب',
            'name.max' => 'اسم القالب يجب أن يكون أقل من 255 حرف',
            'name_ar.max' => 'اسم القالب بالعربية يجب أن يكون أقل من 255 حرف',
            'subject.required' => 'موضوع البريد مطلوب',
            'subject.max' => 'موضوع البريد يجب أن يكون أقل من 500 حرف',
            'body.required' => 'محتوى البريد مطلوب',
            'type.required' => 'نوع القالب مطلوب',
            'type.in' => 'نوع القالب غير صحيح',
        ];
    }
}
