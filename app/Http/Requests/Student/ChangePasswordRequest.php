<?php

namespace App\Http\Requests\Student;

use App\Support\CredentialPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip invisible bidi/format marks that come along when a password is
     * copied out of a WhatsApp credentials message.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'new_password' => CredentialPassword::sanitizeForAuth((string) $this->input('new_password', '')),
            'new_password_confirmation' => CredentialPassword::sanitizeForAuth((string) $this->input('new_password_confirmation', '')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The current password is intentionally not asked for: students routinely
     * forget it, and the session is already authenticated.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $current = $this->user()?->password;

                    if ($current && Hash::check((string) $value, $current)) {
                        $fail('كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية');
                    }
                },
            ],
            'send_credentials' => ['sometimes', 'boolean'],
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
            'new_password' => 'كلمة المرور الجديدة',
            'new_password_confirmation' => 'تأكيد كلمة المرور الجديدة',
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
            'new_password.required' => 'كلمة المرور الجديدة مطلوبة',
            'new_password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'new_password.min' => 'كلمة المرور يجب أن تتكون من 8 أحرف على الأقل',
        ];
    }
}
