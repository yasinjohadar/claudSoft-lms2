<?php

namespace App\WapiAutomation;

/**
 * أسطر متغيرات افتراضية عندما تكون حقول القاعدة فارغة (حتى لا يُرسل components=[])).
 * يجب أن يتطابق عدد المتغيرات لاحقاً مع قالب Meta المعتمد (غالباً سطر واحد = متغير body واحد).
 */
final class WapiAutomationDefaultVariables
{
    /**
     * @return array<int, string>
     */
    public static function bodyLines(string $eventKey): array
    {
        return match ($eventKey) {
            WapiAutomationEventKey::LESSON_COMPLETED => [
                '{student_name}، أكملتَ درس «{lesson_title}» ضمن مساق «{course_title}».',
            ],
            WapiAutomationEventKey::COURSE_COMPLETED => [
                'تهانينا {student_name}! أتممتَ مساق «{course_title}».',
            ],
            WapiAutomationEventKey::QUIZ_COMPLETED => [
                '{student_name}، نتيجة «{quiz_title}»: {score} من {total_questions}.',
            ],
            WapiAutomationEventKey::STUDENT_ENROLLED_IN_COURSE => [
                'مرحباً {student_name}، تم تسجيلك في «{course_title}».',
            ],
            WapiAutomationEventKey::LESSON_BECAME_VISIBLE => [
                'درس جديد متاح: «{lesson_title}» ضمن «{course_title}».',
            ],
            WapiAutomationEventKey::COURSE_GROUP_UPDATED => [
                'تم ربط مساق «{course_title}» بمجموعتك «{group_name}».',
            ],
            WapiAutomationEventKey::GROUP_REGISTRATION_SUBMITTED => [],
            default => [],
        };
    }
}
