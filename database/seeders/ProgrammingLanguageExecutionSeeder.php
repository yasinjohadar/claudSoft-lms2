<?php

namespace Database\Seeders;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Seeder;

class ProgrammingLanguageExecutionSeeder extends Seeder
{
    /**
     * Set execution modes for runnable languages.
     * Run manually: php artisan db:seed --class=ProgrammingLanguageExecutionSeeder
     */
    public function run(): void
    {
        $runtimes = [
            'html' => [
                'monaco_language_id' => 'html',
                'execution_mode' => 'client_web',
                'runtime_slug' => null,
                'file_extension' => 'html',
                'default_filename' => 'index.html',
            ],
            'css' => [
                'monaco_language_id' => 'css',
                'execution_mode' => 'client_web',
                'runtime_slug' => null,
                'file_extension' => 'css',
                'default_filename' => 'style.css',
            ],
            'javascript' => [
                'monaco_language_id' => 'javascript',
                'execution_mode' => 'client_web',
                'runtime_slug' => null,
                'file_extension' => 'js',
                'default_filename' => 'script.js',
            ],
            'python' => [
                'monaco_language_id' => 'python',
                'execution_mode' => 'server',
                'runtime_slug' => 'python',
                'file_extension' => 'py',
                'default_filename' => 'main.py',
            ],
            'php' => [
                'monaco_language_id' => 'php',
                'execution_mode' => 'server',
                'runtime_slug' => 'php',
                'file_extension' => 'php',
                'default_filename' => 'main.php',
            ],
            'java' => [
                'monaco_language_id' => 'java',
                'execution_mode' => 'server',
                'runtime_slug' => 'java',
                'file_extension' => 'java',
                'default_filename' => 'Main.java',
            ],
            'node-js' => [
                'monaco_language_id' => 'javascript',
                'execution_mode' => 'server',
                'runtime_slug' => 'node',
                'file_extension' => 'js',
                'default_filename' => 'main.js',
            ],
        ];

        foreach ($runtimes as $slug => $data) {
            $updated = ProgrammingLanguage::where('slug', $slug)->update($data);
            if ($updated) {
                $this->command?->info("Updated execution config for: {$slug}");
            }
        }

        $this->command?->info('Programming language execution modes configured.');
    }
}
