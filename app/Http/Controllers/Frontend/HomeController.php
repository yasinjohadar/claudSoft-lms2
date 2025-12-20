<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FrontendReview;
use App\Models\FrontendCourse;
use App\Models\BlogPost;
use App\Models\Faq;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get general platform reviews (not linked to specific courses)
        $reviews = FrontendReview::with('user')
                                ->whereNull('frontend_course_id')
                                ->active()
                                ->featured()
                                ->orderBy('order', 'asc')
                                ->orderBy('created_at', 'desc')
                                ->limit(6)
                                ->get();

        $courses = FrontendCourse::with(['category', 'instructor'])
                        ->active()
                        ->published()
                        ->featured()
                        ->orderBy('order', 'asc')
                        ->limit(8)
                        ->get();

        // Get latest blog posts
        $latestPosts = BlogPost::with(['author', 'category', 'tags'])
                              ->published()
                              ->indexable()
                              ->orderBy('published_at', 'desc')
                              ->limit(3)
                              ->get();

        // Get active FAQs
        $faqs = Faq::active()
                   ->ordered()
                   ->limit(6)
                   ->get();

        return view('frontend.pages.index', compact('reviews', 'courses', 'latestPosts', 'faqs'));
    }

    public function reviews()
    {
        $reviews = FrontendReview::active()
                                ->orderBy('created_at', 'desc')
                                ->paginate(12);

        return view('frontend.pages.reviews', compact('reviews'));
    }

    public function contact()
    {
        $settings = \App\Models\ContactSetting::getSettings();
        return view('frontend.pages.contact', compact('settings'));
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        // Here you can send email or save to database
        // For now, we'll just return success

        return back()->with('success', 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');
    }
}
