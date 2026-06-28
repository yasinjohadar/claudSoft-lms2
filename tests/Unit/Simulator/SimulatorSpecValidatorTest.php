<?php

namespace Tests\Unit\Simulator;

use App\Services\Simulator\SimulatorSpecValidator;
use Tests\TestCase;

class SimulatorSpecValidatorTest extends TestCase
{
    private SimulatorSpecValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new SimulatorSpecValidator;
    }

    public function test_valid_spec_passes(): void
    {
        $spec = $this->validSpec();
        $result = $this->validator->validate($spec);

        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
        $this->assertSame([], $result['errors']);
    }

    public function test_missing_meta_fails(): void
    {
        $result = $this->validator->validate(['sections' => []]);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_invalid_section_type_fails(): void
    {
        $spec = $this->validSpec();
        $spec['sections'][] = ['type' => 'unknown_widget'];

        $result = $this->validator->validate($spec);

        $this->assertFalse($result['valid']);
    }

    public function test_interactive_requires_registered_widget(): void
    {
        $spec = $this->validSpec();
        $spec['sections'][] = [
            'type' => 'interactive',
            'widget' => 'not_a_real_widget',
            'config' => [],
        ];

        $result = $this->validator->validate($spec);

        $this->assertFalse($result['valid']);
    }

    public function test_custom_topic_key_passes(): void
    {
        $spec = $this->validSpec();
        $spec['meta']['topic_key'] = 'custom.oop-inheritance-php';

        $result = $this->validator->validate($spec);

        $this->assertTrue($result['valid'], implode(', ', $result['errors']));
    }

    /**
     * @return array<string, mixed>
     */
    private function validSpec(): array
    {
        return [
            'meta' => [
                'topic_key' => 'php.arrays',
                'title' => 'المصفوفات في PHP',
                'languages' => ['php', 'javascript'],
                'level' => 'beginner',
            ],
            'sections' => [
                ['type' => 'hero', 'title' => 'مقدمة', 'summary' => 'ملخص'],
                [
                    'type' => 'concept_cards',
                    'items' => [['title' => 'مصفوفة', 'body' => 'هيكل بيانات']],
                ],
                [
                    'type' => 'code_tabs',
                    'snippets' => [
                        ['lang' => 'php', 'label' => 'PHP', 'code' => '<?php $a = [1,2];'],
                    ],
                ],
                [
                    'type' => 'interactive',
                    'widget' => 'array_playground',
                    'config' => ['operations' => ['push', 'pop'], 'defaultValues' => [1, 2, 3]],
                ],
                [
                    'type' => 'reference_table',
                    'title' => 'دوال',
                    'columns' => ['الدالة', 'الوصف'],
                    'rows' => [['array_push', 'إضافة']],
                ],
                ['type' => 'checklist', 'items' => ['فهم push']],
                [
                    'type' => 'mini_quiz',
                    'questions' => [
                        ['type' => 'mcq', 'prompt' => 'س?', 'options' => ['a', 'b'], 'answer' => 0],
                    ],
                ],
                ['type' => 'callout', 'variant' => 'tip', 'body' => 'نصيحة'],
            ],
        ];
    }
}
