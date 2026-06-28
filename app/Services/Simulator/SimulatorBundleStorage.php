<?php

namespace App\Services\Simulator;

use App\Support\SimulatorKit;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SimulatorBundleStorage
{
    public function diskPath(string $slug): string
    {
        return 'simulators/'.$slug;
    }

    /**
     * @param  array{html: string, css: string, js: string, meta?: array<string, mixed>}  $bundle
     */
    public function save(string $slug, array $bundle): string
    {
        $base = $this->diskPath($slug);
        Storage::disk('local')->put($base.'/index.html', $bundle['html']);

        $css = trim($bundle['css'] ?? '');
        $js = trim($bundle['js'] ?? '');

        if ($css !== '' || $js !== '') {
            Storage::disk('local')->makeDirectory($base.'/assets');
        }

        if ($css !== '') {
            Storage::disk('local')->put($base.'/assets/page.css', $css);
        } else {
            Storage::disk('local')->delete($base.'/assets/page.css');
        }

        if ($js !== '') {
            Storage::disk('local')->put($base.'/assets/simulator.js', $js);
        } else {
            Storage::disk('local')->delete($base.'/assets/simulator.js');
        }

        if (! empty($bundle['meta'])) {
            Storage::disk('local')->put(
                $base.'/meta.json',
                json_encode($bundle['meta'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        }

        return $base;
    }

    /**
     * @return array{html: string, css: string, js: string, meta: array<string, mixed>}|null
     */
    public function load(string $slug): ?array
    {
        $base = $this->diskPath($slug);
        if (! Storage::disk('local')->exists($base.'/index.html')) {
            return null;
        }

        $meta = [];
        if (Storage::disk('local')->exists($base.'/meta.json')) {
            $meta = json_decode(Storage::disk('local')->get($base.'/meta.json'), true) ?? [];
        }

        return [
            'html' => Storage::disk('local')->get($base.'/index.html'),
            'css' => Storage::disk('local')->exists($base.'/assets/page.css')
                ? Storage::disk('local')->get($base.'/assets/page.css')
                : '',
            'js' => Storage::disk('local')->exists($base.'/assets/simulator.js')
                ? Storage::disk('local')->get($base.'/assets/simulator.js')
                : '',
            'meta' => $meta,
        ];
    }

    public function exists(string $slug): bool
    {
        return Storage::disk('local')->exists($this->diskPath($slug).'/index.html');
    }

    public function delete(string $slug): void
    {
        Storage::disk('local')->deleteDirectory($this->diskPath($slug));
    }

    public function playHtml(string $slug, string $assetsBaseUrl = ''): ?string
    {
        $bundle = $this->load($slug);
        if (! $bundle) {
            return null;
        }

        $global = app(SimulatorGlobalAssets::class)->load();

        return SimulatorKit::buildInlinePreviewDocument(
            $bundle['html'],
            $bundle['css'],
            $bundle['js'],
            $global['css'],
            $global['js'],
        );
    }

    public function absolutePath(string $slug, string $file): ?string
    {
        $map = [
            'page.css' => '/assets/page.css',
            'simulator.js' => '/assets/simulator.js',
        ];
        $relative = $map[$file] ?? null;
        if (! $relative) {
            return null;
        }

        $path = storage_path('app/'.$this->diskPath($slug).$relative);

        return File::exists($path) ? $path : null;
    }
}
