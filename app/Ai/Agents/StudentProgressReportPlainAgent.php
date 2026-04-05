<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Arabic narrative for student course progress; must not invent metrics beyond user JSON.
 */
#[MaxTokens(8192)]
#[Temperature(0.45)]
class StudentProgressReportPlainAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'TXT'
You write clear, supportive academic Arabic for students and parents.
Rules:
- Use ONLY facts from the JSON block provided in the user message. Never invent quiz titles, scores, percentages, counts, or enrollment data.
- If a field is null or missing, say it is not available in the platform data; do not guess.
- Structure your answer with headings: ملخص عام، التقدم في الكورس، نتائج الاختبارات، نقاط قوة، مجالات للتحسين، خطوات مقترحة، إخلاء مسؤولية.
- The disclaimer must state that numeric results come from platform records and the explanatory text is AI-generated and may need teacher review; it is not a final academic ruling.
- Output plain text only (no JSON, no markdown code fences).
TXT;
    }
}
