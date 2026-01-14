<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserDeviceController extends Controller
{
    /**
     * Display a listing of all user devices.
     */
    public function index(Request $request)
    {
        try {
            $query = UserDevice::with('user')
                ->latest('last_used_at');

            // Filter by user
            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            // Filter by device type
            if ($request->filled('device_type')) {
                $query->byDeviceType($request->device_type);
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'blocked') {
                    $query->blocked();
                } elseif ($request->status === 'trusted') {
                    $query->trusted();
                } elseif ($request->status === 'active') {
                    $query->active();
                }
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $dateTo = $request->filled('date_to') 
                    ? Carbon::parse($request->date_to)->endOfDay() 
                    : now()->endOfDay();
                $query->whereBetween('last_used_at', [$dateFrom, $dateTo]);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhere('device_fingerprint', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('last_ip_address', 'like', "%{$search}%");
                });
            }

            $devices = $query->paginate($request->get('per_page', 20));

            // Statistics
            $stats = [
                'total' => UserDevice::count(),
                'trusted' => UserDevice::trusted()->count(),
                'blocked' => UserDevice::blocked()->count(),
                'active' => UserDevice::active()->count(),
            ];

            // Get filter options
            $users = User::role('student')->orderBy('name')->get();
            $deviceTypes = ['mobile', 'tablet', 'desktop'];

            return view('admin.user-devices.index', compact(
                'devices',
                'stats',
                'users',
                'deviceTypes'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'حدث خطأ أثناء تحميل الأجهزة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user device.
     */
    public function show($id)
    {
        try {
            $device = UserDevice::with('user')->findOrFail($id);

            // Get related sessions if UserSession model has device_fingerprint or similar
            $relatedSessions = collect([]);
            // This can be implemented later if we add device tracking to sessions

            return view('admin.user-devices.show', compact(
                'device',
                'relatedSessions'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-devices.index')
                ->with('error', 'حدث خطأ أثناء تحميل تفاصيل الجهاز: ' . $e->getMessage());
        }
    }

    /**
     * Display all devices for a specific user.
     */
    public function userDevices($userId, Request $request)
    {
        try {
            $user = User::findOrFail($userId);

            $query = UserDevice::byUser($userId)
                ->latest('last_used_at');

            // Filter by device type
            if ($request->filled('device_type')) {
                $query->byDeviceType($request->device_type);
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'blocked') {
                    $query->blocked();
                } elseif ($request->status === 'trusted') {
                    $query->trusted();
                } elseif ($request->status === 'active') {
                    $query->active();
                }
            }

            $devices = $query->paginate($request->get('per_page', 20));

            // User-specific statistics
            $stats = [
                'total' => UserDevice::byUser($userId)->count(),
                'trusted' => UserDevice::byUser($userId)->trusted()->count(),
                'blocked' => UserDevice::byUser($userId)->blocked()->count(),
                'last_used' => UserDevice::byUser($userId)
                    ->whereNotNull('last_used_at')
                    ->latest('last_used_at')
                    ->first(),
            ];

            return view('admin.user-devices.user', compact(
                'user',
                'devices',
                'stats'
            ));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.user-devices.index')
                ->with('error', 'حدث خطأ أثناء تحميل أجهزة المستخدم: ' . $e->getMessage());
        }
    }

    /**
     * Block a device.
     */
    public function block($id)
    {
        try {
            $device = UserDevice::findOrFail($id);
            
            if ($device->is_blocked) {
                return back()->with('error', 'الجهاز محظور بالفعل.');
            }

            $device->block();

            return back()->with('success', 'تم حظر الجهاز بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حظر الجهاز: ' . $e->getMessage());
        }
    }

    /**
     * Unblock a device.
     */
    public function unblock($id)
    {
        try {
            $device = UserDevice::findOrFail($id);
            
            if (!$device->is_blocked) {
                return back()->with('error', 'الجهاز غير محظور.');
            }

            $device->unblock();

            return back()->with('success', 'تم إلغاء حظر الجهاز بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إلغاء حظر الجهاز: ' . $e->getMessage());
        }
    }

    /**
     * Trust a device.
     */
    public function trust($id)
    {
        try {
            $device = UserDevice::findOrFail($id);
            
            if ($device->is_trusted) {
                return back()->with('error', 'الجهاز موثوق بالفعل.');
            }

            $device->trust();

            return back()->with('success', 'تم تعيين الجهاز كموثوق بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تعيين الثقة: ' . $e->getMessage());
        }
    }

    /**
     * Untrust a device.
     */
    public function untrust($id)
    {
        try {
            $device = UserDevice::findOrFail($id);
            
            if (!$device->is_trusted) {
                return back()->with('error', 'الجهاز غير موثوق.');
            }

            $device->untrust();

            return back()->with('success', 'تم إلغاء الثقة من الجهاز بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إلغاء الثقة: ' . $e->getMessage());
        }
    }

    /**
     * Update device name.
     */
    public function updateDeviceName(Request $request, $id)
    {
        try {
            $request->validate([
                'device_name' => 'nullable|string|max:255',
            ]);

            $device = UserDevice::findOrFail($id);
            $device->update([
                'device_name' => $request->device_name,
            ]);

            return back()->with('success', 'تم تحديث اسم الجهاز بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تحديث اسم الجهاز: ' . $e->getMessage());
        }
    }
}
