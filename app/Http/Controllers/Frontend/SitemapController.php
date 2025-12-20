<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendCourse;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate and return XML sitemap
     */
    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $sitemap .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Homepage
        $sitemap .= $this->addUrl(
            route('frontend.home'),
            now(),
            'daily',
            '1.0'
        );

        // Courses index
        $sitemap .= $this->addUrl(
            route('frontend.courses.index'),
            now(),
            'daily',
            '0.9'
        );

        // Blog index
        $sitemap .= $this->addUrl(
            route('frontend.blog.index'),
            now(),
            'daily',
            '0.9'
        );

        // Individual Courses
        $courses = FrontendCourse::active()
            ->published()
            ->select('slug', 'updated_at')
            ->get();

        foreach ($courses as $course) {
            $sitemap .= $this->addUrl(
                route('frontend.courses.show', $course->slug),
                $course->updated_at,
                'weekly',
                '0.8'
            );
        }

        // Blog Posts
        $posts = BlogPost::published()
            ->indexable()
            ->select('slug', 'updated_at', 'published_at')
            ->get();

        foreach ($posts as $post) {
            $sitemap .= $this->addUrl(
                route('frontend.blog.show', $post->slug),
                $post->updated_at ?? $post->published_at,
                'weekly',
                '0.8'
            );
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Add URL to sitemap
     */
    private function addUrl(string $url, $lastmod, string $changefreq, string $priority): string
    {
        $url = htmlspecialchars($url, ENT_XML1, 'UTF-8');
        $lastmod = $lastmod ? $lastmod->format('Y-m-d\TH:i:s+00:00') : now()->format('Y-m-d\TH:i:s+00:00');

        $xml = "  <url>\n";
        $xml .= "    <loc>{$url}</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }
}

