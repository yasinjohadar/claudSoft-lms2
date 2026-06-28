<?php

namespace App\Services\Simulator;

use Illuminate\Support\Str;

class SimulatorTopicRegistry
{
    public static function all(): array
    {
        return config('simulator-topics.topics', []);
    }

    public static function get(string $topicKey): ?array
    {
        return self::all()[$topicKey] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isCustomKey(string $topicKey): bool
    {
        return str_starts_with($topicKey, 'custom.');
    }

    public static function customKeyFromDescription(string $description): string
    {
        $slug = Str::slug(Str::limit(trim($description), 80, ''));

        return 'custom.'.($slug ?: 'topic-'.Str::random(6));
    }

    public static function label(string $topicKey): string
    {
        return self::get($topicKey)['label'] ?? $topicKey;
    }

    /**
     * @param  list<string>  $languages
     * @return array<string, mixed>
     */
    public static function resolveForGeneration(string $topicKey, string $topicDescription, string $primaryLanguage, array $languages = []): array
    {
        $preset = self::get($topicKey);
        if ($preset) {
            return $preset;
        }

        $allLangs = array_values(array_unique(array_merge([$primaryLanguage], $languages)));

        return [
            'label' => $topicDescription,
            'category' => 'مخصص',
            'subcategory' => config('simulator.primary_languages')[$primaryLanguage] ?? $primaryLanguage,
            'level' => 'beginner',
            'default_widget' => self::suggestWidgetForTopic($topicDescription),
            'default_languages' => $allLangs,
            'primary_language' => $primaryLanguage,
            'is_custom' => true,
            'coverage' => [],
            'prompt_hints' => '',
        ];
    }

    public static function suggestWidgetForTopic(string $description): ?string
    {
        $text = mb_strtolower($description);
        $arrayHints = ['array', 'مصفوف', 'list', 'قائمة', 'collection', 'vector', 'مصفوفات'];

        foreach ($arrayHints as $hint) {
            if (str_contains($text, $hint)) {
                return 'array_playground';
            }
        }

        return null;
    }

    public static function groupedForSelect(): array
    {
        $grouped = [];
        foreach (self::all() as $key => $topic) {
            $category = $topic['category'] ?? 'عام';
            $sub = $topic['subcategory'] ?? '';
            $groupKey = $sub ? "{$category} — {$sub}" : $category;
            $grouped[$groupKey][$key] = $topic['label'] ?? $key;
        }

        return $grouped;
    }
}
