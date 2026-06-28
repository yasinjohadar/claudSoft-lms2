<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachDocumentationPageFromDocsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->input('course_id');

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'placement' => ['required', Rule::in(['reference', 'curriculum'])],
            'section_id' => [
                Rule::requiredIf($this->input('placement') === 'curriculum'),
                'nullable',
                'integer',
                Rule::exists('course_sections', 'id')->where('course_id', $courseId),
            ],
            'additional_course_ids' => ['nullable', 'array'],
            'additional_course_ids.*' => [
                'integer',
                'exists:courses,id',
                Rule::notIn([$courseId]),
            ],
            'lesson_module_ids' => ['nullable', 'array'],
            'lesson_module_ids.*' => ['integer', 'exists:course_modules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'اختر الكورس.',
            'section_id.required' => 'اختر القسم عند الإضافة للمنهج.',
            'placement.in' => 'نوع الربط غير صالح.',
        ];
    }
}
