<?php

namespace Database\Seeders;

use App\Models\ProjectChallenge\ProjectSkill;
use App\Models\ProjectChallenge\ProjectTechnology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectChallengeTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'HTML', 'CSS', 'JavaScript', 'React', 'Laravel', 'PHP', 'Flutter',
            'Node.js', 'API', 'Git', 'Docker', 'SQL', 'Linux', 'UI/UX',
        ];

        foreach ($skills as $name) {
            ProjectSkill::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        $technologies = [
            ['name' => 'Laravel', 'category' => 'framework'],
            ['name' => 'React', 'category' => 'framework'],
            ['name' => 'MySQL', 'category' => 'database'],
            ['name' => 'PostgreSQL', 'category' => 'database'],
            ['name' => 'GitHub', 'category' => 'version_control'],
            ['name' => 'Docker', 'category' => 'hosting'],
            ['name' => 'AWS', 'category' => 'cloud'],
            ['name' => 'Figma', 'category' => 'design'],
        ];

        foreach ($technologies as $tech) {
            ProjectTechnology::firstOrCreate(
                ['slug' => Str::slug($tech['name'])],
                [
                    'name' => $tech['name'],
                    'category' => $tech['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
