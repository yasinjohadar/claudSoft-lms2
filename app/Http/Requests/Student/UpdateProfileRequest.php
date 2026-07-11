<?php

namespace App\Http\Requests\Student;

use App\Models\SiteSetting;
use App\Rules\PhoneMatchesCountryCode;
use App\Rules\UniqueUserFullPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('country_code') && $this->input('country_code') === '') {
            $this->merge(['country_code' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();
        $strict = SiteSetting::isStudentProfileCompletionForced()
            && auth()->user()?->profile_completion_percentage < 100;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => [$strict ? 'required' : 'nullable', 'string', 'max:255'],
            // Email is not included - students cannot change their email
            'country_code' => [$strict ? 'required' : 'nullable', 'string', 'max:8', Rule::in(config('country_codes.allowed_codes'))],
            'phone' => [
                $strict ? 'required' : 'nullable',
                'string',
                'max:20',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                new PhoneMatchesCountryCode,
                new UniqueUserFullPhone($userId ? (int) $userId : null),
            ],
            'date_of_birth' => [$strict ? 'required' : 'nullable', 'date', 'before:today'],
            'gender' => [$strict ? 'required' : 'nullable', 'string', 'in:male,female'],
            'city' => [$strict ? 'required' : 'nullable', 'string', 'max:255'],
            'address' => [$strict ? 'required' : 'nullable', 'string', 'max:500'],
            'nationality_id' => [$strict ? 'required' : 'nullable', 'exists:nationalities,id'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'الاسم بالإنجليزية',
            'name_ar' => 'الاسم بالعربية',
            'email' => 'البريد الإلكتروني',
            'country_code' => 'رمز الدولة',
            'phone' => 'رقم الهاتف',
            'date_of_birth' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'city' => 'المدينة',
            'address' => 'العنوان',
            'nationality_id' => 'الجنسية',
            'photo' => 'الصورة الشخصية',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرف',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل',
            'phone.regex' => 'صيغة رقم الهاتف غير صحيحة',
            'date_of_birth.date' => 'يرجى إدخال تاريخ ميلاد صحيح',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون قبل اليوم',
            'gender.in' => 'يرجى اختيار جنس صحيح',
            'photo.image' => 'الملف يجب أن يكون صورة',
            'photo.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg, jpg, png, gif',
            'photo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
        ];
    }
}
