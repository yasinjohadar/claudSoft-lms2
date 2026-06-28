<?php

namespace App\Services\Simulator;

use App\Support\SimulatorKit;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SimulatorPromptService
{
    /**
     * @param  array{
     *     topic_description?: string,
     *     simulation_details?: string,
     *     primary_language?: string,
     *     languages?: list<string>,
     *     level?: string,
     *     archetype?: string
     * }  $options
     */
    public function buildBundleGenerationPrompt(string $topicKey, array $options = []): string
    {
        $topicDescription = trim($options['topic_description'] ?? '');
        $simulationDetails = trim($options['simulation_details'] ?? '');
        $primaryLanguage = $options['primary_language'] ?? 'php';
        $level = $options['level'] ?? 'beginner';
        $archetype = $options['archetype'] ?? 'playground';
        $primaryLangLabel = config('simulator.primary_languages')[$primaryLanguage] ?? $primaryLanguage;

        $skeletonFile = $archetype === 'stepper'
            ? 'stepper.skeleton.html'
            : 'playground.skeleton.html';
        $skeletonPath = resource_path('simulator-templates/'.$skeletonFile);
        $skeleton = File::exists($skeletonPath) ? File::get($skeletonPath) : '';
        $skeleton = str_replace(
            ['__BUNDLE_ASSETS__', '<!-- SHARED_KIT_CSS -->', '<!-- SHARED_KIT_THEME_JS -->'],
            [SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS, SimulatorKit::sharedCssLinks(), SimulatorKit::themeManagerScript()],
            $skeleton
        );

        $archetypeRules = $archetype === 'stepper'
            ? <<<'RULE'
ARCHETYPE: Code Stepper
- Left: scenario buttons (3–6 scenarios teaching different aspects)
- Center: syntax-highlighted code with active line highlight on step
- Right: variables panel + trace/console output
- Toolbar: Run all, Step, Reset
- Visual area for arrays/objects when relevant
- simulator.js: implement scenarios array, stepIndex, runStep(), reset()
RULE
            : <<<'RULE'
ARCHETYPE: Playground
- Controls panel: inputs, sliders, selects for the topic
- Live preview panel updates in real time
- Code panel shows generated code snippet (HTML/CSS/JS as appropriate)
- simulator.js: wire input events to update preview and codeOutput
RULE;

        $detailsBlock = $simulationDetails !== ''
            ? "Additional requirements from admin:\n{$simulationDetails}"
            : 'Infer sensible controls/scenarios from the topic.';

        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;
        $global = SimulatorKit::PLACEHOLDER_GLOBAL;

        return <<<PROMPT
You are an expert front-end educator building interactive Arabic RTL lesson simulators (same quality as ClaudSoft simulation_langs).

Generate EXACTLY three markdown code blocks — no other text before or after:

```html
(full index.html)
```
```css
(page-specific CSS only — use shared kit for base; add sim-* layout styles)
```
```javascript
(simulator logic — vanilla JS, no frameworks, DOMContentLoaded init)
```

=== TOPIC ===
Title/subject: {$topicDescription}
Stack/language: {$primaryLangLabel} ({$primaryLanguage})
Level: {$level}
topic_key: {$topicKey}

{$archetypeRules}

=== REQUIRED HTML RULES ===
1. lang="ar" dir="rtl" on <html>, data-theme="light"
2. html and body: margin:0; padding:0; width:100%; min-height:100vh — full viewport, no white margins
3. Root container MUST use class "sim-app" with min-height:100vh
4. Google fonts ONLY via <link href="https://fonts.googleapis.com/..."> (never script tags for fonts)
5. Link shared kit CSS using exact placeholder paths (copy from skeleton):
   - href="{$kit}/css/tokens.css" (and base, components, theme-system, utilities)
6. Link page CSS: href="{$assets}/page.css" OR global placeholder href="{$global}/page.css" via __GLOBAL_ASSETS__
7. Theme script: defer src="{$kit}/js/theme-manager.js"
8. Page script: defer src="{$assets}/simulator.js" OR __GLOBAL_ASSETS__/simulator.js when logic is minimal
9. All labels, headings, buttons in Arabic
10. Include <button type="button" class="theme-toggle" aria-label="تبديل الثيم"></button>
11. NEVER use http:// or https:// in script src — ONLY {$kit}, {$assets}, and __GLOBAL_ASSETS__ placeholders for scripts

=== CSS RULES ===
- Style sim-header, sim-main/sim-layout, panels, controls — polished glass/card look
- html, body { margin:0; padding:0; width:100%; min-height:100vh; }
- .sim-app { min-height:100vh; width:100%; }
- Responsive: stack columns on mobile
- Do NOT redefine CSS variables already in tokens.css
- CSS and JS files are REQUIRED for AI generation (interactivity)

=== JS RULES ===
- No eval(), no document.write(), no external script loads
- Use addEventListener, clear functions
- For playground: update #livePreview and #codeOutput (or equivalent ids)
- For stepper: implement at least 3 scenarios with step-by-step execution

{$detailsBlock}

=== SKELETON REFERENCE (follow this structure) ===
{$skeleton}

Generate complete, working files now.
PROMPT;
    }

    /**
     * @param  array{html: string, css: string, js: string}  $bundle
     * @param  array{title?: string}  $options
     */
    public function buildBundleRefinePrompt(array $bundle, string $instructions, array $options = []): string
    {
        $instructions = trim($instructions);
        $title = trim($options['title'] ?? '');
        $kit = SimulatorKit::PLACEHOLDER_KIT;
        $assets = SimulatorKit::PLACEHOLDER_BUNDLE_ASSETS;
        $global = SimulatorKit::PLACEHOLDER_GLOBAL;

        $html = $this->excerptForPrompt($bundle['html'] ?? '', 50000);
        $css = $this->excerptForPrompt($bundle['css'] ?? '', 18000);
        $js = $this->excerptForPrompt($bundle['js'] ?? '', 18000);

        $titleLine = $title !== '' ? "Simulator title: {$title}\n" : '';

        return <<<PROMPT
You are an expert front-end educator editing an existing Arabic RTL interactive lesson simulator (HTML + CSS + JS).

The admin wants SPECIFIC changes. Apply ONLY what they ask; preserve everything else (layout, ids, logic, Arabic copy) unless the instruction requires changing it.

Return EXACTLY three markdown code blocks — no other text:

```html
(complete updated index.html)
```
```css
(complete updated page CSS)
```
```javascript
(complete updated simulator logic)
```

{$titleLine}=== EDITOR INSTRUCTIONS (follow precisely) ===
{$instructions}

=== RULES ===
1. lang="ar" dir="rtl", root class "sim-app", full viewport (html/body margin:0, min-height:100vh)
2. Placeholders only for local assets: {$kit}, {$assets}, {$global}
3. Google Fonts via <link> only; NO external CDN scripts
4. No eval(), no document.write()
5. Return COMPLETE files (not diffs), ready to run
6. Keep existing functionality unless instructions say to remove/change it

=== CURRENT HTML ===
```html
{$html}
```

=== CURRENT CSS ===
```css
{$css}
```

=== CURRENT JS ===
```javascript
{$js}
```

Apply the editor instructions and return the three complete updated files.
PROMPT;
    }

    private function excerptForPrompt(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return Str::limit($text, $limit)."\n\n/* ... truncated for prompt; preserve structure and apply edits consistently ... */";
    }

    /**
     * @param  array{
     *     topic_description?: string,
     *     primary_language?: string,
     *     languages?: list<string>,
     *     level?: string,
     *     full_coverage?: bool
     * }  $options
     */
    public function buildGenerationPrompt(string $topicKey, array $options = []): string
    {
        $topicDescription = trim($options['topic_description'] ?? '');
        $primaryLanguage = $options['primary_language'] ?? 'php';
        $languages = $options['languages'] ?? [];
        $level = $options['level'] ?? 'beginner';
        $fullCoverage = (bool) ($options['full_coverage'] ?? true);

        $topic = SimulatorTopicRegistry::resolveForGeneration(
            $topicKey,
            $topicDescription ?: SimulatorTopicRegistry::label($topicKey),
            $primaryLanguage,
            $languages,
        );

        $topicLabel = $topic['label'] ?? $topicDescription;
        $languages = $languages ?: ($topic['default_languages'] ?? [$primaryLanguage]);
        $languagesList = $this->formatList($languages);
        $primaryLangLabel = config('simulator.primary_languages')[$primaryLanguage] ?? $primaryLanguage;

        $sectionTypes = implode(', ', config('simulator.section_types', []));
        $widgets = collect(config('simulator.widgets', []))
            ->filter(fn ($w) => ($w['phase'] ?? 1) <= 1)
            ->keys()
            ->implode(', ');

        $suggestedWidget = $topic['default_widget'] ?? SimulatorTopicRegistry::suggestWidgetForTopic($topicDescription);
        $interactiveRule = $suggestedWidget === 'array_playground'
            ? 'Include ONE "interactive" section with widget "array_playground" and config.operations covering all relevant list/array operations for this topic.'
            : 'Do NOT include an "interactive" section unless the topic is clearly about arrays/lists/collections. Instead add extra concept_cards and a larger reference_table.';

        $coverage = $topic['coverage'] ?? [];
        $methods = implode(', ', $coverage['methods'] ?? []);
        $operations = implode(', ', $coverage['operations'] ?? []);

        $schemaExcerpt = <<<'JSON'
{
  "meta": { "topic_key": "...", "title": "...", "languages": ["php"], "level": "beginner" },
  "sections": [
    { "type": "hero", "title": "...", "summary": "..." },
    { "type": "concept_cards", "items": [{ "title": "...", "body": "...", "icon": "layers" }] },
    { "type": "code_tabs", "snippets": [{ "lang": "php", "label": "PHP", "code": "...", "highlights": [1] }] },
    { "type": "reference_table", "title": "...", "columns": ["المفهوم","الوصف","مثال"], "rows": [["...","...","..."]] },
    { "type": "comparison", "pairs": [{ "title": "...", "body": "..." }] },
    { "type": "checklist", "items": ["..."] },
    { "type": "mini_quiz", "questions": [{ "type": "mcq", "prompt": "...", "options": ["a","b","c","d"], "answer": 0 }] },
    { "type": "callout", "variant": "tip", "body": "..." }
  ]
}
JSON;

        $coverageRule = '';
        if ($fullCoverage) {
            if (! empty($methods) || ! empty($operations)) {
                $coverageRule = "COMPREHENSIVE COVERAGE (preset topic):\n"
                    ."- Include EVERY listed method/function in reference_table.\n"
                    ."- Methods: {$methods}\n"
                    ."- Widget operations (if using array_playground): {$operations}";
            } else {
                $coverageRule = <<<'RULE'
COMPREHENSIVE COVERAGE (required — custom/open topic):
- Research the topic deeply and cover ALL important concepts, subtopics, syntax, keywords, operators, functions, methods, properties, patterns, and best practices.
- Split complex topics into multiple concept_cards sections (8–15 cards total across one or two concept_cards sections).
- reference_table must be exhaustive: every relevant API/syntax element as rows (minimum 15 rows for intermediate+ topics).
- code_tabs: practical, runnable examples in the primary language; add snippets for other selected languages when they differ meaningfully.
- checklist: one item per sub-concept (minimum 10 items).
- mini_quiz: minimum 5 questions testing understanding across the full topic.
- Include comparison section when multiple languages/stacks are selected or when comparing approaches (e.g. let vs const, SQL JOIN types).
- Explain common mistakes and pitfalls in callout sections (at least 2 callouts: tip + warning).
RULE;
            }
        }

        $hints = $topic['prompt_hints'] ?? '';

        return <<<PROMPT
You are an expert programming educator. Generate a complete interactive lesson simulator as a single JSON object (no markdown fences, no commentary).

=== TOPIC REQUEST ===
Subject / idea: {$topicLabel}
Primary language/stack: {$primaryLangLabel} ({$primaryLanguage})
Additional languages for code_tabs: {$languagesList}
Difficulty level: {$level}
topic_key to use in meta: {$topicKey}

=== CONTENT REQUIREMENTS ===
1. Write ALL explanatory text in Arabic (RTL-friendly). Keep code in the correct programming language.
2. The simulator must teach the topic from fundamentals to practical usage — suitable for self-study.
3. Cover the FULL scope of "{$topicLabel}" — do not skip important subtopics.
4. Output ONLY valid JSON matching the schema below. Start with `{` and end with `}` — no markdown, no preamble.
5. Use ONLY these section types: {$sectionTypes}
6. Allowed interactive widgets: {$widgets}
7. {$interactiveRule}
8. Include at least: hero, concept_cards (multiple items), code_tabs, reference_table, checklist, mini_quiz (5+ questions), callout (2+).
{$coverageRule}
{$hints}

Schema excerpt:
{$schemaExcerpt}

Generate the complete, comprehensive spec now.
PROMPT;
    }

    public function buildJsonRepairPrompt(string $brokenResponse): string
    {
        $excerpt = Str::limit($brokenResponse, 14000);

        return <<<PROMPT
You previously generated a lesson simulator spec but the JSON was invalid or incomplete.

Return ONLY one valid JSON object (no markdown fences, no commentary, no text before or after).
The object MUST have "meta" (object) and "sections" (array).

Fix syntax errors, close all brackets, escape strings properly, and complete any truncated structure.
Keep Arabic explanatory text; keep code snippets accurate.

Broken / partial output to fix:
{$excerpt}
PROMPT;
    }

    /**
     * @param  list<string>  $items
     */
    private function formatList(array $items): string
    {
        return implode(', ', $items);
    }
}
