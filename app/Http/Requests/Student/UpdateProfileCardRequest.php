<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('student');
    }

    public function rules(): array
    {
        $cardId = auth()->user()->profileCard?->id;
        $presets = array_keys(config('profile-card.themes', []));
        $platforms = array_keys(config('profile-card.social_platforms', []));

        return [
            'slug' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('student_profile_cards', 'slug')->ignore($cardId),
            ],
            'job_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['nullable', 'boolean'],
            'qr_enabled' => ['nullable', 'boolean'],
            'theme.preset' => ['nullable', 'string', Rule::in($presets)],
            'theme.accent_color' => ['nullable', 'string', 'max:20'],
            'theme.card_style' => ['nullable', 'string', 'max:30'],
            'social_links' => ['nullable', 'array', 'max:20'],
            'social_links.*.platform' => ['nullable', 'string', Rule::in($platforms)],
            'social_links.*.url' => ['nullable', 'string', 'max:500'],
            'social_links.*.icon' => ['nullable', 'string', 'max:100'],
            'social_links.*.label' => ['nullable', 'string', 'max:100'],
            'social_links.*.enabled' => ['nullable', 'boolean'],
            'social_links.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'الرابط المختصر يجب أن يحتوي على حروف إنجليزية صغيرة وأرقام وشرطات فقط.',
            'slug.unique' => 'هذا الرابط المختصر مستخدم بالفعل.',
        ];
    }
}
