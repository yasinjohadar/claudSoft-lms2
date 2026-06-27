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
            // Frontend
            'HTML', 'CSS', 'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular',
            'Next.js', 'Tailwind CSS', 'Bootstrap', 'SASS/SCSS', 'Responsive Design',
            'UI/UX', 'Accessibility (a11y)', 'Web Performance',
            // Backend
            'PHP', 'Laravel', 'Node.js', 'Express.js', 'Python', 'Django', 'REST API',
            'GraphQL', 'Authentication', 'Authorization', 'Microservices',
            // Mobile
            'Flutter', 'Dart', 'React Native', 'Android', 'iOS',
            // Database & DevOps
            'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Database Design',
            'Git', 'GitHub', 'Docker', 'CI/CD', 'Linux', 'AWS', 'Deployment',
            // Soft & Project
            'Agile/Scrum', 'Team Collaboration', 'Technical Writing', 'Problem Solving',
            'System Design', 'Testing', 'Debugging', 'Security Basics',
        ];

        foreach ($skills as $name) {
            ProjectSkill::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        $technologies = [
            // Languages
            ['name' => 'PHP', 'category' => 'language'],
            ['name' => 'JavaScript', 'category' => 'language'],
            ['name' => 'TypeScript', 'category' => 'language'],
            ['name' => 'Python', 'category' => 'language'],
            ['name' => 'Dart', 'category' => 'language'],
            // Frameworks
            ['name' => 'Laravel', 'category' => 'framework'],
            ['name' => 'React', 'category' => 'framework'],
            ['name' => 'Vue.js', 'category' => 'framework'],
            ['name' => 'Angular', 'category' => 'framework'],
            ['name' => 'Next.js', 'category' => 'framework'],
            ['name' => 'Flutter', 'category' => 'framework'],
            ['name' => 'Express.js', 'category' => 'framework'],
            ['name' => 'Django', 'category' => 'framework'],
            // Databases
            ['name' => 'MySQL', 'category' => 'database'],
            ['name' => 'PostgreSQL', 'category' => 'database'],
            ['name' => 'MongoDB', 'category' => 'database'],
            ['name' => 'Redis', 'category' => 'database'],
            ['name' => 'SQLite', 'category' => 'database'],
            // Tools & Cloud
            ['name' => 'GitHub', 'category' => 'version_control'],
            ['name' => 'GitLab', 'category' => 'version_control'],
            ['name' => 'Docker', 'category' => 'devops'],
            ['name' => 'Nginx', 'category' => 'devops'],
            ['name' => 'AWS', 'category' => 'cloud'],
            ['name' => 'Google Cloud', 'category' => 'cloud'],
            ['name' => 'Vercel', 'category' => 'hosting'],
            ['name' => 'Coolify', 'category' => 'hosting'],
            // Design & APIs
            ['name' => 'Figma', 'category' => 'design'],
            ['name' => 'Postman', 'category' => 'api'],
            ['name' => 'Stripe', 'category' => 'payment'],
            ['name' => 'Firebase', 'category' => 'backend'],
            ['name' => 'Supabase', 'category' => 'backend'],
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

        $this->command?->info('✓ المهارات: ' . ProjectSkill::count() . ' | التقنيات: ' . ProjectTechnology::count());
    }
}
