<?php

namespace App\Support;

use App\Models\GroupMembershipRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipRequestFilters
{
    /**
     * @return array<string, array{label: string, type: string, options?: array<string, string>, column?: string, placeholder?: string}>
     */
    public static function formFilterDefinitions(): array
    {
        return [
            'reg_name' => ['label' => 'الاسم بالإنجليزية', 'type' => 'text', 'column' => 'name', 'placeholder' => 'بحث جزئي...'],
            'reg_name_ar' => ['label' => 'الاسم بالعربية', 'type' => 'text', 'column' => 'name_ar', 'placeholder' => 'بحث جزئي...'],
            'reg_nationality_id' => ['label' => 'الجنسية', 'type' => 'nationality', 'column' => 'nationality_id'],
            'reg_gender' => [
                'label' => 'الجنس',
                'type' => 'select',
                'column' => 'gender',
                'options' => ['male' => 'ذكر', 'female' => 'أنثى', 'other' => 'أخرى'],
            ],
            'reg_city' => ['label' => 'المدينة', 'type' => 'text', 'column' => 'city', 'placeholder' => 'بحث جزئي...'],
            'reg_dob_from' => ['label' => 'تاريخ الميلاد من', 'type' => 'date', 'column' => 'date_of_birth'],
            'reg_dob_to' => ['label' => 'تاريخ الميلاد إلى', 'type' => 'date', 'column' => 'date_of_birth'],
            'reg_has_computer' => [
                'label' => 'يمتلك حاسوب',
                'type' => 'select',
                'column' => 'has_computer',
                'options' => ['yes' => 'نعم', 'no' => 'لا'],
            ],
            'reg_commitment' => [
                'label' => 'الالتزام بالتدريب',
                'type' => 'select',
                'column' => 'commitment_to_training',
                'options' => ['yes' => 'نعم', 'no' => 'لا'],
            ],
            'reg_sufficient_time' => [
                'label' => 'وقت كافٍ للمتابعة',
                'type' => 'select',
                'column' => 'has_sufficient_time',
                'options' => ['yes' => 'نعم', 'no' => 'لا'],
            ],
            'reg_computer_exp' => [
                'label' => 'خبرة الحاسوب',
                'type' => 'select',
                'column' => 'computer_experience_level',
                'options' => [
                    'none' => 'بدون',
                    'beginner' => 'مبتدئ',
                    'intermediate' => 'متوسط',
                    'good' => 'جيد',
                    'advanced' => 'متقدم',
                ],
            ],
            'reg_prog_exp' => [
                'label' => 'خبرة البرمجة',
                'type' => 'select',
                'column' => 'programming_experience',
                'options' => [
                    'none' => 'بدون',
                    'beginner' => 'مبتدئ',
                    'intermediate' => 'متوسط',
                    'expert' => 'خبير',
                ],
            ],
            'reg_bootcamp' => [
                'label' => 'الاهتمام بالمعسكر',
                'type' => 'select',
                'column' => 'interested_in_bootcamp',
                'options' => ['yes' => 'نعم', 'no' => 'لا'],
            ],
            'reg_education_level' => ['label' => 'آخر مرحلة دراسية', 'type' => 'text', 'column' => 'education_level', 'placeholder' => 'مثال: جامعة'],
            'reg_education_major' => ['label' => 'التخصص الدراسي', 'type' => 'text', 'column' => 'education_major', 'placeholder' => 'بحث جزئي...'],
            'reg_current_job' => ['label' => 'العمل الحالي', 'type' => 'text', 'column' => 'current_job', 'placeholder' => 'بحث جزئي...'],
            'reg_has_form' => [
                'label' => 'بيانات الفورم',
                'type' => 'select',
                'column' => '_has_form',
                'options' => ['yes' => 'لديه فورم', 'no' => 'بدون فورم'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function sortOptions(): array
    {
        return [
            'created_at' => 'تاريخ الطلب',
            'id' => 'رقم الطلب',
            'payment_date' => 'موعد تسديد الرسوم',
            'status' => 'حالة الطلب',
            'student_name' => 'اسم الطالب',
            'reg_name' => 'الاسم بالإنجليزية (فورم)',
            'reg_name_ar' => 'الاسم بالعربية (فورم)',
            'reg_dob' => 'تاريخ الميلاد',
            'reg_computer_exp' => 'خبرة الحاسوب',
            'reg_prog_exp' => 'خبرة البرمجة',
            'reg_education_level' => 'آخر مرحلة دراسية',
        ];
    }

    public static function apply(Builder $query, int $groupId, Request $request): Builder
    {
        self::applySearch($query, $groupId, $request);
        self::applyRequestDateFilters($query, $request);
        self::applyPaymentDateFilters($query, $request);
        self::applyOtherGroupsFilter($query, $groupId, $request);
        self::applyFormFilters($query, $groupId, $request);
        self::applySort($query, $groupId, $request);

        return $query;
    }

    public static function activeFilterCount(Request $request): int
    {
        $count = 0;
        $keys = array_merge(
            ['search', 'status', 'other_groups', 'wa_membership', 'request_from', 'request_to', 'payment_from', 'payment_to'],
            array_keys(self::formFilterDefinitions())
        );

        foreach ($keys as $key) {
            if ($request->filled($key)) {
                $count++;
            }
        }

        if ($request->filled('sort_by') && $request->input('sort_by') !== 'created_at') {
            $count++;
        }

        if ($request->filled('sort_order') && $request->input('sort_order') !== 'desc') {
            $count++;
        }

        return $count;
    }

    private static function applySearch(Builder $query, int $groupId, Request $request): void
    {
        if (! $request->filled('search')) {
            return;
        }

        $search = trim((string) $request->search);
        $like = '%'.$search.'%';

        $query->where(function (Builder $outer) use ($like, $groupId) {
            $outer->whereHas('student', function (Builder $q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('name_ar', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            })->orWhereExists(function ($sub) use ($like, $groupId) {
                self::wrapRegistrationMatch($sub, $groupId);
                $sub->where(function ($reg) use ($like) {
                    $reg->where('gr.name', 'like', $like)
                        ->orWhere('gr.name_ar', 'like', $like)
                        ->orWhere('gr.email', 'like', $like)
                        ->orWhere('gr.phone', 'like', $like)
                        ->orWhere('gr.city', 'like', $like)
                        ->orWhere('gr.education_level', 'like', $like)
                        ->orWhere('gr.education_major', 'like', $like)
                        ->orWhere('gr.current_job', 'like', $like)
                        ->orWhere('gr.notes', 'like', $like);
                });
            });
        });
    }

    private static function applyRequestDateFilters(Builder $query, Request $request): void
    {
        if ($request->filled('request_from')) {
            $query->whereDate('group_membership_requests.created_at', '>=', $request->input('request_from'));
        }
        if ($request->filled('request_to')) {
            $query->whereDate('group_membership_requests.created_at', '<=', $request->input('request_to'));
        }
    }

    private static function applyPaymentDateFilters(Builder $query, Request $request): void
    {
        if ($request->filled('payment_from')) {
            $query->whereDate('group_membership_requests.payment_date', '>=', $request->input('payment_from'));
        }
        if ($request->filled('payment_to')) {
            $query->whereDate('group_membership_requests.payment_date', '<=', $request->input('payment_to'));
        }
    }

    private static function applyOtherGroupsFilter(Builder $query, int $groupId, Request $request): void
    {
        if (! $request->filled('other_groups')) {
            return;
        }

        $value = $request->input('other_groups');
        if (! in_array($value, ['yes', 'no'], true)) {
            return;
        }

        $table = (new GroupMembershipRequest)->getTable();

        if ($value === 'yes') {
            $query->whereExists(function ($sub) use ($groupId, $table) {
                $sub->select(DB::raw('1'))
                    ->from('course_group_members as cgm_other')
                    ->whereColumn('cgm_other.student_id', $table.'.student_id')
                    ->where('cgm_other.group_id', '!=', $groupId);
            });
        } else {
            $query->whereNotExists(function ($sub) use ($groupId, $table) {
                $sub->select(DB::raw('1'))
                    ->from('course_group_members as cgm_other')
                    ->whereColumn('cgm_other.student_id', $table.'.student_id')
                    ->where('cgm_other.group_id', '!=', $groupId);
            });
        }
    }

    private static function applyFormFilters(Builder $query, int $groupId, Request $request): void
    {
        $definitions = self::formFilterDefinitions();
        $regFilters = [];

        foreach ($definitions as $param => $def) {
            if ($param === 'reg_has_form') {
                continue;
            }
            if (! $request->filled($param)) {
                continue;
            }

            $value = trim((string) $request->input($param));
            if ($value === '') {
                continue;
            }

            $regFilters[$param] = ['def' => $def, 'value' => $value];
        }

        if ($request->filled('reg_has_form')) {
            $hasForm = $request->input('reg_has_form');
            if ($hasForm === 'yes') {
                $query->whereExists(fn ($sub) => self::wrapRegistrationMatch($sub, $groupId));
            } elseif ($hasForm === 'no') {
                $query->whereNotExists(fn ($sub) => self::wrapRegistrationMatch($sub, $groupId));
            }
        }

        if ($regFilters === []) {
            return;
        }

        $query->whereExists(function ($sub) use ($groupId, $regFilters) {
            self::wrapRegistrationMatch($sub, $groupId);

            foreach ($regFilters as $param => $payload) {
                $def = $payload['def'];
                $value = $payload['value'];
                $column = $def['column'];

                if ($param === 'reg_dob_from') {
                    $sub->whereDate('gr.date_of_birth', '>=', $value);
                } elseif ($param === 'reg_dob_to') {
                    $sub->whereDate('gr.date_of_birth', '<=', $value);
                } elseif ($def['type'] === 'text') {
                    $sub->where('gr.'.$column, 'like', '%'.$value.'%');
                } else {
                    $sub->where('gr.'.$column, $value);
                }
            }
        });
    }

    private static function applySort(Builder $query, int $groupId, Request $request): void
    {
        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $table = (new GroupMembershipRequest)->getTable();

        if ($sortBy === 'student_name') {
            $query->leftJoin('users as mr_sort_users', 'mr_sort_users.id', '=', $table.'.student_id')
                ->orderBy('mr_sort_users.name', $sortOrder)
                ->select($table.'.*');

            return;
        }

        $registrationColumns = [
            'reg_name' => 'name',
            'reg_name_ar' => 'name_ar',
            'reg_dob' => 'date_of_birth',
            'reg_computer_exp' => 'computer_experience_level',
            'reg_prog_exp' => 'programming_experience',
            'reg_education_level' => 'education_level',
        ];

        if (isset($registrationColumns[$sortBy])) {
            $column = $registrationColumns[$sortBy];
            $query->orderBy(
                self::registrationValueSubquery($groupId, $column),
                $sortOrder
            );

            return;
        }

        $allowed = ['created_at', 'id', 'payment_date', 'status'];
        if (! in_array($sortBy, $allowed, true)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($table.'.'.$sortBy, $sortOrder);
    }

    private static function registrationValueSubquery(int $groupId, string $column)
    {
        return DB::table('group_registrations as gr_sort')
            ->select('gr_sort.'.$column)
            ->where('gr_sort.group_id', $groupId)
            ->where(function ($match) {
                $match->whereColumn('gr_sort.user_id', 'group_membership_requests.student_id')
                    ->orWhereIn('gr_sort.email', function ($emailSub) {
                        $emailSub->select('email')
                            ->from('users')
                            ->whereColumn('users.id', 'group_membership_requests.student_id')
                            ->whereNotNull('email');
                    });
            })
            ->orderByDesc('gr_sort.id')
            ->limit(1);
    }

    private static function wrapRegistrationMatch($sub, int $groupId): void
    {
        $sub->select(DB::raw('1'))
            ->from('group_registrations as gr')
            ->where('gr.group_id', $groupId)
            ->where(function ($match) {
                $match->whereColumn('gr.user_id', 'group_membership_requests.student_id')
                    ->orWhereIn('gr.email', function ($emailSub) {
                        $emailSub->select('email')
                            ->from('users')
                            ->whereColumn('users.id', 'group_membership_requests.student_id')
                            ->whereNotNull('email');
                    });
            });
    }
}
