<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulator topic registry (extensible tree)
    |--------------------------------------------------------------------------
    |
    | Each topic defines coverage requirements for AI generation.
    |
    */
    'topics' => [
        'php.arrays' => [
            'label' => 'المصفوفات في PHP',
            'category' => 'برمجة',
            'subcategory' => 'PHP',
            'level' => 'beginner',
            'default_widget' => 'array_playground',
            'default_languages' => ['php', 'javascript', 'python'],
            'coverage' => [
                'methods' => [
                    'array_push', 'array_pop', 'array_shift', 'array_unshift',
                    'array_merge', 'array_combine', 'array_slice', 'array_splice',
                    'array_map', 'array_filter', 'array_reduce', 'array_walk',
                    'array_keys', 'array_values', 'array_flip', 'array_reverse',
                    'array_search', 'array_key_exists', 'in_array', 'count',
                    'sort', 'rsort', 'asort', 'arsort', 'ksort', 'krsort',
                    'array_unique', 'array_diff', 'array_intersect',
                ],
                'properties' => [],
                'operations' => ['push', 'pop', 'shift', 'unshift', 'map', 'filter', 'reduce', 'slice', 'reverse', 'sort'],
            ],
            'prompt_hints' => 'Include reference_table rows for every PHP array function listed. Use array_playground widget with all operations.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flat list helper — built at runtime via SimulatorTopicRegistry
    |--------------------------------------------------------------------------
    */
];
