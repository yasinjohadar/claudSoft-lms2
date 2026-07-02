<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class ActivityLogQueryService
{
    /**
     * @return array<string, string>
     */
    public static function logNameLabels(): array
    {
        return [
            'users' => 'المستخدمون',
            'courses' => 'الكورسات',
            'finance' => 'المالية',
            'camps' => 'المعسكرات',
            'security' => 'الأمان',
            'settings' => 'الإعدادات',
            'default' => 'عام',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function eventLabels(): array
    {
        return [
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
        ];
    }

    public function query(Request $request)
    {
        $query = Activity::query()
            ->with(['causer'])
            ->latest('created_at');

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_type', User::class)
                ->where('causer_id', $request->input('causer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('query')) {
            $term = '%'.$request->input('query').'%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('subject_type', 'like', $term)
                    ->orWhere('properties', 'like', $term);
            });
        }

        return $query;
    }

    public function paginate(Request $request, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query($request)->paginate($perPage)->withQueryString();
    }

    public function subjectLabel(Activity $activity): string
    {
        if (! $activity->subject_type) {
            return '—';
        }

        $short = class_basename($activity->subject_type);
        $id = $activity->subject_id ?? '?';

        return "{$short} #{$id}";
    }

    /**
     * @return list<array{field: string, old: mixed, new: mixed}>
     */
    public function diffRows(Activity $activity): array
    {
        $properties = $activity->properties?->toArray() ?? [];
        $old = $properties['old'] ?? [];
        $new = $properties['attributes'] ?? [];

        if ($activity->event === 'created') {
            $rows = [];
            foreach ($new as $field => $value) {
                if (in_array($field, ['password', 'remember_token'], true)) {
                    continue;
                }
                $rows[] = ['field' => $field, 'old' => null, 'new' => $value];
            }

            return $rows;
        }

        if ($activity->event === 'deleted') {
            $rows = [];
            foreach ($old as $field => $value) {
                if (in_array($field, ['password', 'remember_token'], true)) {
                    continue;
                }
                $rows[] = ['field' => $field, 'old' => $value, 'new' => null];
            }

            return $rows;
        }

        $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
        $rows = [];
        foreach ($fields as $field) {
            if (in_array($field, ['password', 'remember_token', 'updated_at'], true)) {
                continue;
            }
            $oldVal = $old[$field] ?? null;
            $newVal = $new[$field] ?? null;
            if ($oldVal === $newVal) {
                continue;
            }
            $rows[] = ['field' => $field, 'old' => $oldVal, 'new' => $newVal];
        }

        return $rows;
    }
}
