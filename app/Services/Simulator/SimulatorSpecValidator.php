<?php

namespace App\Services\Simulator;

class SimulatorSpecValidator
{
    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate(mixed $spec): array
    {
        $errors = [];

        if (! is_array($spec)) {
            return ['valid' => false, 'errors' => ['Spec must be a JSON object.']];
        }

        if (! isset($spec['meta']) || ! is_array($spec['meta'])) {
            $errors[] = 'Missing or invalid meta object.';
        } else {
            $errors = array_merge($errors, $this->validateMeta($spec['meta']));
        }

        if (! isset($spec['sections']) || ! is_array($spec['sections'])) {
            $errors[] = 'Missing or invalid sections array.';
        } elseif (count($spec['sections']) < 1) {
            $errors[] = 'At least one section is required.';
        } else {
            foreach ($spec['sections'] as $i => $section) {
                $errors = array_merge($errors, $this->validateSection($section, (int) $i));
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * @return list<string>
     */
    private function validateMeta(array $meta): array
    {
        $errors = [];
        $required = ['topic_key', 'title', 'languages', 'level'];

        foreach ($required as $field) {
            if (empty($meta[$field])) {
                $errors[] = "meta.{$field} is required.";
            }
        }

        if (isset($meta['languages']) && (! is_array($meta['languages']) || count($meta['languages']) < 1)) {
            $errors[] = 'meta.languages must be a non-empty array.';
        }

        $levels = array_keys(config('simulator.levels', []));
        if (isset($meta['level']) && ! in_array($meta['level'], $levels, true)) {
            $errors[] = 'meta.level must be one of: '.implode(', ', $levels);
        }

        if (isset($meta['topic_key']) && ! SimulatorTopicRegistry::get($meta['topic_key']) && ! SimulatorTopicRegistry::isCustomKey($meta['topic_key'])) {
            $errors[] = "Unknown topic_key: {$meta['topic_key']}";
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateSection(mixed $section, int $index): array
    {
        $errors = [];
        $prefix = "sections[{$index}]";

        if (! is_array($section)) {
            return ["{$prefix} must be an object."];
        }

        $type = $section['type'] ?? null;
        $allowed = config('simulator.section_types', []);

        if (! $type || ! in_array($type, $allowed, true)) {
            $errors[] = "{$prefix}.type must be one of: ".implode(', ', $allowed);

            return $errors;
        }

        return match ($type) {
            'hero' => empty($section['title']) ? ["{$prefix}.title is required for hero."] : [],
            'concept_cards' => $this->validateItems($section, $prefix, ['title', 'body']),
            'code_tabs' => $this->validateSnippets($section, $prefix),
            'interactive' => $this->validateInteractive($section, $prefix),
            'reference_table' => $this->validateReferenceTable($section, $prefix),
            'checklist' => (! isset($section['items']) || ! is_array($section['items']) || count($section['items']) < 1)
                ? ["{$prefix}.items must be a non-empty array."]
                : [],
            'mini_quiz' => $this->validateMiniQuiz($section, $prefix),
            'callout' => empty($section['body']) ? ["{$prefix}.body is required for callout."] : [],
            'comparison' => $this->validateComparison($section, $prefix),
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    private function validateItems(array $section, string $prefix, array $requiredFields): array
    {
        if (! isset($section['items']) || ! is_array($section['items']) || count($section['items']) < 1) {
            return ["{$prefix}.items must be a non-empty array."];
        }

        $errors = [];
        foreach ($section['items'] as $i => $item) {
            if (! is_array($item)) {
                $errors[] = "{$prefix}.items[{$i}] must be an object.";
                continue;
            }
            foreach ($requiredFields as $field) {
                if (empty($item[$field])) {
                    $errors[] = "{$prefix}.items[{$i}].{$field} is required.";
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateSnippets(array $section, string $prefix): array
    {
        if (! isset($section['snippets']) || ! is_array($section['snippets']) || count($section['snippets']) < 1) {
            return ["{$prefix}.snippets must be a non-empty array."];
        }

        $errors = [];
        foreach ($section['snippets'] as $i => $snippet) {
            if (! is_array($snippet)) {
                $errors[] = "{$prefix}.snippets[{$i}] must be an object.";
                continue;
            }
            foreach (['lang', 'label', 'code'] as $field) {
                if (empty($snippet[$field])) {
                    $errors[] = "{$prefix}.snippets[{$i}].{$field} is required.";
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateInteractive(array $section, string $prefix): array
    {
        $errors = [];
        $widget = $section['widget'] ?? null;
        $widgets = config('simulator.widgets', []);

        if (! $widget || ! isset($widgets[$widget])) {
            $errors[] = "{$prefix}.widget must be a registered widget.";

            return $errors;
        }

        if (($widgets[$widget]['phase'] ?? 1) > 1) {
            $errors[] = "{$prefix}.widget {$widget} is not available in phase 1.";
        }

        if (! isset($section['config']) || ! is_array($section['config'])) {
            $errors[] = "{$prefix}.config must be an object.";
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateReferenceTable(array $section, string $prefix): array
    {
        $errors = [];
        if (empty($section['title'])) {
            $errors[] = "{$prefix}.title is required for reference_table.";
        }
        if (! isset($section['columns']) || ! is_array($section['columns']) || count($section['columns']) < 1) {
            $errors[] = "{$prefix}.columns must be a non-empty array.";
        }
        if (! isset($section['rows']) || ! is_array($section['rows'])) {
            $errors[] = "{$prefix}.rows must be an array.";
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateMiniQuiz(array $section, string $prefix): array
    {
        if (! isset($section['questions']) || ! is_array($section['questions']) || count($section['questions']) < 1) {
            return ["{$prefix}.questions must be a non-empty array."];
        }

        $errors = [];
        foreach ($section['questions'] as $i => $q) {
            if (! is_array($q)) {
                $errors[] = "{$prefix}.questions[{$i}] must be an object.";
                continue;
            }
            if (($q['type'] ?? '') !== 'mcq') {
                $errors[] = "{$prefix}.questions[{$i}].type must be mcq.";
            }
            if (empty($q['prompt'])) {
                $errors[] = "{$prefix}.questions[{$i}].prompt is required.";
            }
            if (! isset($q['options']) || ! is_array($q['options']) || count($q['options']) < 2) {
                $errors[] = "{$prefix}.questions[{$i}].options must have at least 2 items.";
            }
            if (! isset($q['answer']) || ! is_int($q['answer'])) {
                $errors[] = "{$prefix}.questions[{$i}].answer must be an integer index.";
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateComparison(array $section, string $prefix): array
    {
        if (! isset($section['pairs']) || ! is_array($section['pairs']) || count($section['pairs']) < 1) {
            return ["{$prefix}.pairs must be a non-empty array."];
        }

        return [];
    }
}
