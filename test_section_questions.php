<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$section = App\Models\CourseSection::with(['questions.questionType', 'questions.options'])->find(7);

echo "Section: " . $section->title . "\n";
echo "Questions count: " . $section->questions->count() . "\n\n";

foreach ($section->questions as $question) {
    echo "ID: " . $question->id . "\n";
    echo "Text: " . substr(strip_tags($question->question_text), 0, 100) . "\n";
    echo "Type: " . $question->questionType->display_name . "\n";
    echo "Grade: " . $question->pivot->question_grade . "\n";
    echo "---\n";
}
