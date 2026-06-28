<?php

return [

    'spec_version' => '1.0',

    /*
    |--------------------------------------------------------------------------
    | Allowed section types (closed list — phase 1)
    |--------------------------------------------------------------------------
    */
    'section_types' => [
        'hero',
        'concept_cards',
        'code_tabs',
        'interactive',
        'reference_table',
        'comparison',
        'checklist',
        'mini_quiz',
        'callout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interactive widget registry
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'array_playground' => [
            'label' => 'ملعب المصفوفات',
            'script' => 'js/simulator-engine/widgets/array_playground.js',
        ],
        // Phase 2
        'flexbox_playground' => [
            'label' => 'ملعب Flexbox',
            'script' => 'js/simulator-engine/widgets/flexbox_playground.js',
            'phase' => 2,
        ],
        'sql_result_grid' => [
            'label' => 'شبكة نتائج SQL',
            'script' => 'js/simulator-engine/widgets/sql_result_grid.js',
            'phase' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported code tab languages
    |--------------------------------------------------------------------------
    */
    'code_languages' => ['php', 'javascript', 'python', 'css', 'sql', 'html', 'bash'],

    /*
    |--------------------------------------------------------------------------
    | Difficulty levels
    |--------------------------------------------------------------------------
    */
    'levels' => [
        'beginner' => 'مبتدئ',
        'intermediate' => 'متوسط',
        'advanced' => 'متقدم',
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary language/stack options (AI wizard)
    |--------------------------------------------------------------------------
    */
    'primary_languages' => [
        'php' => 'PHP',
        'javascript' => 'JavaScript',
        'python' => 'Python',
        'typescript' => 'TypeScript',
        'java' => 'Java',
        'csharp' => 'C#',
        'cpp' => 'C++',
        'css' => 'CSS',
        'html' => 'HTML',
        'sql' => 'SQL',
        'bash' => 'Bash / Shell',
        'git' => 'Git',
        'docker' => 'Docker',
        'react' => 'React',
        'laravel' => 'Laravel',
        'nodejs' => 'Node.js',
        'other' => 'أخرى (حدّد في وصف الموضوع)',
    ],

    'default_render_mode' => 'html_bundle',

    'archetypes' => [
        'playground' => 'Playground — controls + live preview + code',
        'stepper' => 'Code Stepper — scenarios + step/run + trace',
    ],

    'bundle' => [
        'storage_disk' => 'local',
        'storage_prefix' => 'simulators',
    ],

    'global_assets' => [
        'public_path' => 'simulator-kit/global',
        'css_file' => 'page.css',
        'js_file' => 'simulator.js',
    ],

];
