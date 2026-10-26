<?php

namespace App\Http\Requests\Mobile\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'يرجى اختيار صورة',
            'avatar.image' => 'الملف يجب أن يكون صورة',
            'avatar.mimes' => 'الصورة يجب أن تكون بصيغة: jpeg, jpg, png, gif, webp',
            'avatar.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
        ];
    }
}
