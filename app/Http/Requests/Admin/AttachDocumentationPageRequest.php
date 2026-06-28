<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachDocumentationPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('course')?->id ?? $this->route('course');

        return [
            'documentation_page_ids' => ['required', 'array', 'min:1'],
            'documentation_page_ids.*' => [
                'integer',
                Rule::exists('documentation_pages', 'id')->where(function ($query) {
                    $query->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                }),
            ],
            'placement' => ['required', Rule::in(['reference', 'curriculum'])],
            'section_id' => [
                Rule::requiredIf($this->input('placement') === 'curriculum'),
                'nullable',
                'integer',
                Rule::exists('course_sections', 'id')->where('course_id', $courseId),
            ],
            'additional_course_ids' => ['nullable', 'array'],
            'additional_course_ids.*' => ['integer', 'exists:courses,id'],
            'lesson_module_ids' => ['nullable', 'array'],
            'lesson_module_ids.*' => ['integer', 'exists:course_modules,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'documentation_page_ids.required' => 'اختر صفحة توثيق واحدة على الأقل.',
            'section_id.required' => 'اختر القسم عند الإضافة للمنهج.',
            'placement.in' => 'نوع الربط غير صالح.',
        ];
    }
}
