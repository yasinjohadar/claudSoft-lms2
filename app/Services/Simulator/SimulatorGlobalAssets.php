<?php

namespace App\Services\Simulator;

use Illuminate\Support\Facades\File;

class SimulatorGlobalAssets
{
    public function directory(): string
    {
        return public_path('simulator-kit/global');
    }

    public function cssPath(): string
    {
        return $this->directory().'/page.css';
    }

    public function jsPath(): string
    {
        return $this->directory().'/simulator.js';
    }

    public function ensureDefaults(): void
    {
        $dir = $this->directory();
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (! File::exists($this->cssPath())) {
            File::put($this->cssPath(), $this->defaultCss());
        }

        if (! File::exists($this->jsPath())) {
            File::put($this->jsPath(), $this->defaultJs());
        }
    }

    /**
     * @return array{css: string, js: string}
     */
    public function load(): array
    {
        $this->ensureDefaults();

        return [
            'css' => File::get($this->cssPath()),
            'js' => File::get($this->jsPath()),
        ];
    }

    public function save(string $css, string $js): void
    {
        $this->ensureDefaults();
        File::put($this->cssPath(), $css);
        File::put($this->jsPath(), $js);
    }

    public function cssUrl(): string
    {
        return asset('simulator-kit/global/page.css');
    }

    public function jsUrl(): string
    {
        return asset('simulator-kit/global/simulator.js');
    }

    private function defaultCss(): string
    {
        return <<<'CSS'
/* ملف CSS مركزي — يُطبَّق على جميع محاكيات HTML */
.sim-app {
    min-height: 100vh;
}
CSS;
    }

    private function defaultJs(): string
    {
        return <<<'JS'
// ملف JS مركزي — يُحمَّل تلقائياً مع كل محاكاة HTML
document.addEventListener('DOMContentLoaded', function () {
    // أضف منطقك المشترك هنا
});
JS;
    }
}
