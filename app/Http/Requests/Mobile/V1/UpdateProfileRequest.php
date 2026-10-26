<?php

namespace App\Http\Requests\Mobile\V1;

use App\Http\Requests\Student\UpdateProfileRequest as StudentUpdateProfileRequest;

/**
 * نفس قواعد تعديل الملف الشخصي في لوحة الطالب، مع فصل رفع الصورة في نقطة مستقلة.
 */
class UpdateProfileRequest extends StudentUpdateProfileRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['photo']);

        return $rules;
    }
}
