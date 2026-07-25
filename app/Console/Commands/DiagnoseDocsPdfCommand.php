<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnoseDocsPdfCommand extends Command
{
    protected $signature = 'docs:pdf-diagnose';

    protected $description = 'Diagnose Browsershot / Chromium setup for documentation PDF export';

    public function handle(): int
    {
        $this->info('Documentation PDF diagnostics');
        $this->newLine();

        $nodeModules = base_path('node_modules');
        $puppeteer = $nodeModules.DIRECTORY_SEPARATOR.'puppeteer';
        $this->line('node_modules: '.(is_dir($nodeModules) ? 'OK' : 'MISSING'));
        $this->line('puppeteer package: '.(is_dir($puppeteer) ? 'OK' : 'MISSING (run npm install)'));

        $configured = (string) config('browsershot.chrome_path');
        $this->line('BROWSERSHOT_CHROME_PATH: '.($configured !== '' ? $configured : '(empty)'));
        $this->line('BROWSERSHOT_NO_SANDBOX: '.(config('browsershot.no_sandbox') ? 'true' : 'false'));

        $candidates = array_values(array_filter([
            $configured,
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ]));

        $found = null;
        foreach (array_unique($candidates) as $path) {
            $ok = is_file($path) || is_executable($path);
            $this->line(($ok ? '[OK] ' : '[--] ').$path);
            if ($ok && $found === null) {
                $found = $path;
            }
        }

        $this->newLine();
        if ($found === null) {
            $this->error('No Chromium/Chrome binary found. PDF export will fail on this host.');
            $this->line('Coolify: ensure nixpacks.toml is deployed, or install chromium in the image and set:');
            $this->line('  BROWSERSHOT_CHROME_PATH=/usr/bin/chromium');
            $this->line('  BROWSERSHOT_NO_SANDBOX=true');

            return self::FAILURE;
        }

        $this->info('Usable Chrome path: '.$found);

        return self::SUCCESS;
    }
}
