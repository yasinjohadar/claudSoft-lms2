<?php

namespace App\Services\QuestionBank\TypeImport;

class TypeImportColumnRegistry
{
    /**
     * @return list<string>
     */
    public static function supportedTypes(): array
    {
        return [
            'multiple_choice_single',
            'multiple_choice_multiple',
            'true_false',
            'short_answer',
            'essay',
            'matching',
            'fill_blanks',
            'ordering',
            'numerical',
            'calculated',
        ];
    }

    public static function isSupported(string $typeName): bool
    {
        return in_array($typeName, self::supportedTypes(), true);
    }

    /**
     * @return array<string, string> Arabic label => internal key
     */
    public static function commonColumns(): array
    {
        return [
            'نص السؤال' => 'question_text',
            'اسم الدرس' => 'lesson_name',
            'الدرجة' => 'points',
            'الصعوبة' => 'difficulty',
            'الكورس' => 'course',
            'الشرح' => 'explanation',
            'العلامات' => 'tags',
            'اللغة البرمجية' => 'language',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function typeSpecificColumns(string $typeName): array
    {
        return match ($typeName) {
            'multiple_choice_single', 'multiple_choice_multiple' => [
                'الخيار 1' => 'option_1',
                'الخيار 2' => 'option_2',
                'الخيار 3' => 'option_3',
                'الخيار 4' => 'option_4',
                'الخيار 5' => 'option_5',
                'الخيار 6' => 'option_6',
                'الإجابة الصحيحة' => 'correct_answer',
            ],
            'true_false' => [
                'الإجابة الصحيحة' => 'correct_answer',
            ],
            'short_answer', 'fill_blanks' => [
                'إجابات مقبولة' => 'accepted_answers',
                'حساس لحالة الأحرف' => 'case_sensitive',
            ],
            'matching' => [
                'أزواج المطابقة' => 'matching_pairs_raw',
            ],
            'ordering' => [
                'الخيار 1' => 'option_1',
                'الخيار 2' => 'option_2',
                'الخيار 3' => 'option_3',
                'الخيار 4' => 'option_4',
                'الخيار 5' => 'option_5',
                'الخيار 6' => 'option_6',
            ],
            'numerical' => [
                'الإجابة الصحيحة' => 'correct_answer',
                'هامش الخطأ' => 'tolerance',
                'الوحدة' => 'unit',
            ],
            'calculated' => [
                'الإجابة الصحيحة' => 'correct_answer',
                'هامش الخطأ' => 'tolerance',
                'الوحدة' => 'unit',
                'معادلة' => 'formula',
            ],
            'essay' => [
                'الحد الأدنى للكلمات' => 'min_words',
                'الحد الأقصى للكلمات' => 'max_words',
                'إجابة نموذجية' => 'model_answer',
                'معايير التقييم' => 'grading_criteria',
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function headersForType(string $typeName): array
    {
        return array_merge(self::commonColumns(), self::typeSpecificColumns($typeName));
    }

    /**
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public static function commonGuideRows(): array
    {
        return [
            ['الكورس', 'اسم كورس منشور في النظام', 'اختياري إذا حددت الكورس من واجهة الاستيراد'],
            ['اللغة البرمجية', 'اسم اللغة كما في النظام', 'اختياري إذا حددت اللغة من واجهة الاستيراد'],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public static function guideRowsForType(string $typeName): array
    {
        return array_merge(self::commonGuideRows(), match ($typeName) {
            'multiple_choice_single' => [
                ['الخيارات 1–6', 'نص كل خيار في عموده', 'على الأقل خياران'],
                ['الإجابة الصحيحة', 'رقم الخيار الصحيح', 'مثال: 1'],
            ],
            'multiple_choice_multiple' => [
                ['الخيارات 1–6', 'نص كل خيار', 'على الأقل خياران'],
                ['الإجابة الصحيحة', 'أرقام الخيارات الصحيحة مفصولة بفاصلة', 'مثال: 1,3'],
            ],
            'true_false' => [
                ['الإجابة الصحيحة', 'true أو false أو 1 أو 2 أو صح أو خطأ', 'لا حاجة لأعمدة الخيارات'],
            ],
            'short_answer' => [
                ['إجابات مقبولة', 'بدائل مفصولة بـ |', 'مثال: 4|أربعة|٤'],
                ['حساس لحالة الأحرف', 'نعم أو لا', 'اختياري'],
            ],
            'fill_blanks' => [
                ['نص السؤال', 'استخدم [[blank]] أو ___ للفراغات', 'مطلوب'],
                ['إجابات مقبولة', 'بدائل مفصولة بـ | حسب ترتيب الفراغات', 'مطلوب'],
            ],
            'matching' => [
                ['أزواج المطابقة', 'سؤال1||إجابة1;;;سؤال2||إجابة2', 'استخدم || و;;;'],
            ],
            'ordering' => [
                ['الخيار 1–6', 'العناصر بالترتيب الصحيح', 'على الأقل عنصران'],
            ],
            'numerical' => [
                ['الإجابة الصحيحة', 'قيمة رقمية', 'مطلوب'],
                ['هامش الخطأ', 'هامش مقبول', 'اختياري'],
                ['الوحدة', 'وحدة القياس', 'اختياري'],
            ],
            'calculated' => [
                ['الإجابة الصحيحة', 'النتيجة الرقمية المتوقعة', 'مطلوب'],
                ['معادلة', 'معادلة الحساب', 'اختياري'],
            ],
            'essay' => [
                ['الحد الأدنى/الأقصى للكلمات', 'أعداد صحيحة', 'اختياري'],
                ['إجابة نموذجية ومعايير التقييم', 'للمرجعية عند التصحيح اليدوي', 'اختياري'],
            ],
            default => [],
        });
    }

    /**
     * @return list<array<string, string>>
     */
    public static function exampleRowsForType(string $typeName): array
    {
        return match ($typeName) {
            'multiple_choice_single' => [
                [
                    'question_text' => 'ما عاصمة السعودية؟',
                    'lesson_name' => 'درس الجغرافيا',
                    'option_1' => 'الرياض',
                    'option_2' => 'جدة',
                    'option_3' => 'الدمام',
                    'option_4' => 'مكة',
                    'correct_answer' => '1',
                    'points' => '1',
                    'difficulty' => 'easy',
                ],
                [
                    'question_text' => 'ما لغة البرمجة المستخدمة في Laravel؟',
                    'lesson_name' => 'أساسيات PHP',
                    'option_1' => 'PHP',
                    'option_2' => 'Python',
                    'option_3' => 'Java',
                    'option_4' => 'Ruby',
                    'correct_answer' => '1',
                    'points' => '1',
                    'difficulty' => 'medium',
                ],
            ],
            'multiple_choice_multiple' => [
                [
                    'question_text' => 'اختر اللغات البرمجية',
                    'option_1' => 'PHP',
                    'option_2' => 'Python',
                    'option_3' => 'C#',
                    'option_4' => 'HTML',
                    'correct_answer' => '1,2',
                    'points' => '2',
                    'difficulty' => 'medium',
                ],
            ],
            'true_false' => [
                [
                    'question_text' => 'الشمس تشرق من الغرب',
                    'correct_answer' => 'false',
                    'points' => '1',
                    'difficulty' => 'easy',
                ],
            ],
            'short_answer' => [
                [
                    'question_text' => 'ما ناتج 2+2؟',
                    'accepted_answers' => '4|أربعة|٤',
                    'case_sensitive' => 'لا',
                    'points' => '1',
                    'difficulty' => 'easy',
                ],
            ],
            'fill_blanks' => [
                [
                    'question_text' => 'عاصمة السعودية [[blank]]',
                    'accepted_answers' => 'الرياض',
                    'case_sensitive' => 'لا',
                    'points' => '1',
                    'difficulty' => 'easy',
                ],
            ],
            'matching' => [
                [
                    'question_text' => 'طابق المصطلحات',
                    'matching_pairs_raw' => 'متغير||variable;;;دالة||function',
                    'points' => '2',
                    'difficulty' => 'medium',
                ],
            ],
            'ordering' => [
                [
                    'question_text' => 'رتّب مراحل الماء',
                    'option_1' => 'تبخر',
                    'option_2' => 'تكثف',
                    'option_3' => 'هطول',
                    'option_4' => 'جريان',
                    'points' => '2',
                    'difficulty' => 'easy',
                ],
            ],
            'numerical' => [
                [
                    'question_text' => 'ما ناتج 15 × 8؟',
                    'correct_answer' => '120',
                    'tolerance' => '5',
                    'unit' => '',
                    'points' => '1',
                    'difficulty' => 'easy',
                ],
            ],
            'calculated' => [
                [
                    'question_text' => 'احسب الناتج',
                    'correct_answer' => '100',
                    'tolerance' => '0',
                    'formula' => '2*50',
                    'points' => '2',
                    'difficulty' => 'medium',
                ],
            ],
            'essay' => [
                [
                    'question_text' => 'ناقش مفهوم البرمجة كائنية التوجه',
                    'min_words' => '50',
                    'max_words' => '500',
                    'model_answer' => 'نموذج للمدرس',
                    'grading_criteria' => 'المحتوى والأسلوب',
                    'points' => '10',
                    'difficulty' => 'medium',
                ],
            ],
            default => [],
        };
    }
}
