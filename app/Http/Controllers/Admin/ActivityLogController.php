<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\ActivityLogQueryService;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogQueryService $queryService,
    ) {
        $this->middleware('auth');
        $this->middleware('permission:activity-log-view');
    }

    public function index(Request $request)
    {
        $activities = $this->queryService->paginate($request);
        $logNameLabels = ActivityLogQueryService::logNameLabels();
        $eventLabels = ActivityLogQueryService::eventLabels();
        $admins = User::role('admin')->orderBy('name')->get(['id', 'name', 'email']);

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.activity-logs._table', compact(
                    'activities',
                    'logNameLabels',
                    'eventLabels',
                ))->render(),
                'count' => $activities->total(),
            ]);
        }

        return view('admin.pages.activity-logs.index', compact(
            'activities',
            'logNameLabels',
            'eventLabels',
            'admins',
        ));
    }

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);
        $logNameLabels = ActivityLogQueryService::logNameLabels();
        $eventLabels = ActivityLogQueryService::eventLabels();
        $diffRows = $this->queryService->diffRows($activity);

        return view('admin.pages.activity-logs.show', compact(
            'activity',
            'logNameLabels',
            'eventLabels',
            'diffRows',
        ));
    }
}
