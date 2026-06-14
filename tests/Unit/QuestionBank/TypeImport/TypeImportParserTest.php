<?php

namespace Tests\Unit\QuestionBank\TypeImport;

use App\Models\QuestionType;
use App\Services\QuestionBank\TypeImport\Json\FlatJsonParser;
use App\Services\QuestionBank\TypeImport\Json\JsonImportParser;
use App\Services\QuestionBank\TypeImport\Json\StructuredJsonParser;
use App\Services\QuestionBank\TypeImport\TypeImportColumnRegistry;
use PHPUnit\Framework\TestCase;

class TypeImportParserTest extends TestCase
{
    private function makeType(string $name, string $displayName): QuestionType
    {
        $type = new QuestionType;
        $type->name = $name;
        $type->display_name = $displayName;

        return $type;
    }

    public function test_supported_types_count(): void
    {
        $this->assertCount(10, TypeImportColumnRegistry::supportedTypes());
    }

    public function test_structured_json_parser_maps_multiple_choice(): void
    {
        $type = $this->makeType('multiple_choice_single', 'اختيار من متعدد (إجابة واحدة)');
        $payload = [
            'version' => '1.0',
            'question_type' => 'multiple_choice_single',
            'questions' => [
                [
                    'question_text' => 'سؤال؟',
                    'course' => 'كورس',
                    'options' => [
                        ['text' => 'أ', 'is_correct' => true],
                        ['text' => 'ب', 'is_correct' => false],
                    ],
                ],
            ],
        ];

        $rows = (new StructuredJsonParser)->parse($payload, $type);

        $this->assertCount(1, $rows);
        $data = $rows[0]->toArray();
        $this->assertSame('سؤال؟', $data['question_text']);
        $this->assertSame('أ', $data['option_1']);
        $this->assertSame('1', $data['correct_answer']);
    }

    public function test_structured_json_rejects_mismatched_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $type = $this->makeType('multiple_choice_single', 'اختيار من متعدد (إجابة واحدة)');
        $payload = [
            'version' => '1.0',
            'question_type' => 'matching',
            'questions' => [],
        ];

        (new StructuredJsonParser)->parse($payload, $type);
    }

    public function test_flat_json_parser_accepts_root_array(): void
    {
        $type = $this->makeType('short_answer', 'إجابة قصيرة');
        $payload = [
            [
                'question_text' => 'ناتج 2+2',
                'course' => 'رياضيات',
                'accepted_answers' => '4|أربعة',
            ],
        ];

        $rows = (new FlatJsonParser)->parse($payload, $type);

        $this->assertCount(1, $rows);
        $this->assertSame('4|أربعة', $rows[0]->toArray()['accepted_answers']);
    }

    public function test_json_import_parser_detects_structured_payload(): void
    {
        $type = $this->makeType('matching', 'مطابقة');
        $json = json_encode([
            'version' => '1.0',
            'question_type' => 'matching',
            'questions' => [
                [
                    'question_text' => 'طابق',
                    'course' => 'كورس',
                    'matching_pairs' => [
                        ['question' => 'أ', 'answer' => '1'],
                    ],
                ],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $rows = (new JsonImportParser)->parse($json, $type);

        $this->assertCount(1, $rows);
        $this->assertStringContainsString('أ||1', $rows[0]->toArray()['matching_pairs_raw']);
    }
}
