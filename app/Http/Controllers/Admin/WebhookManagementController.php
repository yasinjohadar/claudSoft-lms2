<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use App\Models\WPFormsSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookManagementController extends Controller
{
    /**
     * Display webhooks dashboard with statistics
     */
    public function index(Request $request)
    {
        // Statistics
        $stats = [
            'total_submissions' => WPFormsSubmission::count(),
            'pending' => WPFormsSubmission::pending()->count(),
            'processed' => WPFormsSubmission::processed()->count(),
            'failed' => WPFormsSubmission::failed()->count(),
            'today' => WPFormsSubmission::whereDate('created_at', today())->count(),
            'this_week' => WPFormsSubmission::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_logs' => WebhookLog::count(),
        ];

        // By submission type
        $byType = WPFormsSubmission::select('submission_type', DB::raw('count(*) as count'))
            ->groupBy('submission_type')
            ->get();

        // Recent submissions
        $recentSubmissions = WPFormsSubmission::with(['user', 'course'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent webhook logs
        $recentLogs = WebhookLog::latest()
            ->limit(10)
            ->get();

        return view('admin.webhooks.index', compact('stats', 'byType', 'recentSubmissions', 'recentLogs'));
    }

    /**
     * Display all submissions with filtering
     */
    public function submissions(Request $request)
    {
        $query = WPFormsSubmission::with(['user', 'course'])->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('submission_type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('form_id', 'like', "%{$request->search}%")
                  ->orWhere('entry_id', 'like', "%{$request->search}%");
            });
        }

        $submissions = $query->paginate(20);

        $types = WPFormsSubmission::getSubmissionTypes();

        return view('admin.webhooks.submissions', compact('submissions', 'types'));
    }

    /**
     * Display single submission details
     */
    public function showSubmission(WPFormsSubmission $submission)
    {
        $submission->load(['user', 'course']);

        return view('admin.webhooks.submission-details', compact('submission'));
    }

    /**
     * Display webhook logs with filtering
     */
    public function logs(Request $request)
    {
        $query = WebhookLog::latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $logs = $query->paginate(20);

        return view('admin.webhooks.logs', compact('logs'));
    }

    /**
     * Display single webhook log details
     */
    public function showLog(WebhookLog $log)
    {
        return view('admin.webhooks.log-details', compact('log'));
    }

    /**
     * Retry failed submission
     */
    public function retrySubmission(WPFormsSubmission $submission)
    {
        if ($submission->status !== 'failed') {
            return back()->with('error', 'يمكن فقط إعادة محاولة الإرساليات الفاشلة');
        }

        try {
            // Re-process the submission
            $controller = new \App\Http\Controllers\Api\WebhookController();

            // Create a mock request with the submission data
            $mockRequest = Request::create('/api/webhooks/wpforms', 'POST', [
                'form_id' => $submission->form_id,
                'entry_id' => $submission->entry_id,
                'fields' => $submission->form_data,
            ]);

            // Reset submission status
            $submission->update(['status' => 'pending']);

            // Process again
            $result = $controller->wpforms($mockRequest);

            return back()->with('success', 'تمت إعادة المحاولة بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'فشلت إعادة المحاولة: ' . $e->getMessage());
        }
    }

    /**
     * Delete old webhook logs
     */
    public function cleanupLogs(Request $request)
    {
        $days = $request->input('days', 30);

        $deleted = WebhookLog::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "تم حذف {$deleted} سجل قديم");
    }

    /**
     * Export submissions to CSV
     */
    public function export(Request $request)
    {
        $submissions = WPFormsSubmission::with(['user', 'course'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('submission_type', $request->type))
            ->get();

        $filename = 'webhook_submissions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, ['ID', 'Form ID', 'Entry ID', 'Type', 'Status', 'User', 'Course', 'Created At']);

            // Data
            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->form_id,
                    $submission->entry_id,
                    $submission->submission_type,
                    $submission->status,
                    $submission->user?->name ?? '-',
                    $submission->course?->title ?? '-',
                    $submission->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
