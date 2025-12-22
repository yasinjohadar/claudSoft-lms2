<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendCourse;
use App\Models\FrontendCourseCategory;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $categories = FrontendCourseCategory::active()
                                    ->orderBy('order', 'asc')
                                    ->get();

        $query = FrontendCourse::with(['category', 'instructor'])
                      ->active()
                      ->published();

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by level
        if ($request->has('level') && $request->level != '') {
            $query->where('level', $request->level);
        }

        // Filter by price type
        if ($request->has('price_type')) {
            if ($request->price_type == 'free') {
                $query->where('is_free', true);
            } elseif ($request->price_type == 'paid') {
                $query->where('is_free', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('students_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $courses = $query->paginate(12);

        return view('frontend.pages.courses', compact('courses', 'categories'));
    }

    public function show($slug)
    {
        $course = FrontendCourse::with(['category', 'instructor', 'sections.lessons'])
                       ->where('slug', $slug)
                       ->active()
                       ->published()
                       ->firstOrFail();

        // Increment views count
        $course->incrementViews();

        // Get course reviews
        $reviews = $course->reviews()
                         ->where('is_active', true)
                         ->orderBy('is_featured', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->limit(10)
                         ->get();

        // Get related courses from same category
        $relatedCourses = FrontendCourse::with(['category', 'instructor'])
                               ->where('category_id', $course->category_id)
                               ->where('id', '!=', $course->id)
                               ->active()
                               ->published()
                               ->limit(4)
                               ->get();

        // Get social media links from contact settings
        $contactSettings = ContactSetting::getSettings();
        $socialLinks = collect($contactSettings->social_links ?? [])
            ->where('enabled', true)
            ->map(function($link) use ($course) {
                // Replace URL with share links if needed
                $url = $link['url'] ?? '#';
                if ($url === '#' || empty($url)) {
                    // Generate share URL based on platform
                    $courseUrl = url()->route('frontend.courses.show', $course->slug);
                    $courseTitle = $course->title;
                    
                    switch($link['platform']) {
                        case 'facebook':
                            $url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($courseUrl);
                            break;
                        case 'twitter':
                            $url = 'https://twitter.com/intent/tweet?url=' . urlencode($courseUrl) . '&text=' . urlencode($courseTitle);
                            break;
                        case 'whatsapp':
                            $url = 'https://wa.me/?text=' . urlencode($courseTitle . ' ' . $courseUrl);
                            break;
                        case 'telegram':
                            $url = 'https://t.me/share/url?url=' . urlencode($courseUrl) . '&text=' . urlencode($courseTitle);
                            break;
                    }
                }
                $link['url'] = $url;
                return $link;
            });

        return view('frontend.pages.course-details', compact('course', 'relatedCourses', 'reviews', 'socialLinks'));
    }
}
