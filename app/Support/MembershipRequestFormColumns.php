<?php

namespace App\Support;

use App\Models\GroupRegistration;

class MembershipRequestFormColumns
{
    /**
     * @return array<string, array{label: string, group: string, default?: bool}>
     */
    public static function definitions(): array
    {
        return [
            'reg_name_en' => ['label' => 'الاسم بالإنجليزية (فورم)', 'group' => 'form'],
            'reg_name_ar' => ['label' => 'الاسم بالعربية (فورم)', 'group' => 'form'],
            'reg_nationality' => ['label' => 'الجنسية', 'group' => 'form'],
            'reg_dob' => ['label' => 'تاريخ الميلاد', 'group' => 'form'],
            'reg_gender' => ['label' => 'الجنس', 'group' => 'form'],
            'reg_city' => ['label' => 'المدينة', 'group' => 'form'],
            'reg_address' => ['label' => 'العنوان', 'group' => 'form'],
            'reg_has_computer' => ['label' => 'يمتلك حاسوب', 'group' => 'form'],
            'reg_commitment' => ['label' => 'الالتزام بالتدريب', 'group' => 'form'],
            'reg_sufficient_time' => ['label' => 'وقت كافٍ للمتابعة', 'group' => 'form'],
            'reg_computer_exp' => ['label' => 'خبرة الحاسوب', 'group' => 'form'],
            'reg_prog_exp' => ['label' => 'خبرة البرمجة', 'group' => 'form'],
            'reg_bootcamp' => ['label' => 'الاهتمام بالمعسكر', 'group' => 'form'],
            'reg_education_level' => ['label' => 'آخر مرحلة دراسية', 'group' => 'form'],
            'reg_education_major' => ['label' => 'التخصص الدراسي', 'group' => 'form'],
            'reg_current_job' => ['label' => 'العمل الحالي', 'group' => 'form'],
            'reg_prog_background' => ['label' => 'نبذة الحاسوب والبرمجة', 'group' => 'form'],
            'reg_notes' => ['label' => 'ملاحظات الطالب', 'group' => 'form'],
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultVisibility(): array
    {
        $defaults = [
            'id' => true,
            'student' => true,
            'other_groups' => true,
            'email' => true,
            'phone' => true,
            'whatsapp' => true,
            'request_date' => true,
            'payment_date' => false,
            'form' => true,
            'status' => true,
        ];

        foreach (array_keys(self::definitions()) as $key) {
            $defaults[$key] = false;
        }

        return $defaults;
    }

    public static function displayValue(?GroupRegistration $registration, string $columnKey): string
    {
        if (! $registration) {
            return '—';
        }

        return match ($columnKey) {
            'reg_name_en' => self::text($registration->name),
            'reg_name_ar' => self::text($registration->name_ar),
            'reg_nationality' => self::text($registration->nationality?->name),
            'reg_dob' => $registration->date_of_birth?->format('Y-m-d') ?? '—',
            'reg_gender' => match ($registration->gender) {
                'male' => 'ذكر',
                'female' => 'أنثى',
                'other' => 'أخرى',
                default => '—',
            },
            'reg_city' => self::text($registration->city),
            'reg_address' => self::text($registration->address),
            'reg_has_computer' => self::yesNo($registration->has_computer),
            'reg_commitment' => self::yesNo($registration->commitment_to_training),
            'reg_sufficient_time' => self::yesNo($registration->has_sufficient_time),
            'reg_computer_exp' => self::computerLevel($registration->computer_experience_level),
            'reg_prog_exp' => self::progLevel($registration->programming_experience),
            'reg_bootcamp' => self::yesNo($registration->interested_in_bootcamp),
            'reg_education_level' => self::text($registration->education_level),
            'reg_education_major' => self::text($registration->education_major),
            'reg_current_job' => self::text($registration->current_job),
            'reg_prog_background' => self::text($registration->computer_programming_background, 80),
            'reg_notes' => self::text($registration->notes, 80),
            default => '—',
        };
    }

    private static function text(?string $value, ?int $limit = null): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return '—';
        }

        return $limit ? \Illuminate\Support\Str::limit($value, $limit) : $value;
    }

    private static function yesNo(?string $value): string
    {
        return match ($value) {
            'yes' => 'نعم',
            'no' => 'لا',
            default => '—',
        };
    }

    private static function computerLevel(?string $value): string
    {
        return match ($value) {
            'none' => 'بدون',
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'good' => 'جيد',
            'advanced' => 'متقدم',
            default => self::text($value),
        };
    }

    private static function progLevel(?string $value): string
    {
        return match ($value) {
            'none' => 'بدون',
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'expert' => 'خبير',
            default => self::text($value),
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\GroupMembershipRequest>  $requests
     * @return array<int, GroupRegistration|null>
     */
    public static function mapRegistrationsForRequests(int $groupId, $requests): array
    {
        if ($requests->isEmpty()) {
            return [];
        }

        $studentIds = $requests->pluck('student_id')->filter()->unique()->values();
        $emails = $requests
            ->map(fn ($r) => $r->student?->email)
            ->filter()
            ->map(fn ($e) => mb_strtolower(trim($e)))
            ->unique()
            ->values();

        $registrations = GroupRegistration::query()
            ->where('group_id', $groupId)
            ->where(function ($q) use ($studentIds, $emails) {
                if ($studentIds->isNotEmpty()) {
                    $q->whereIn('user_id', $studentIds);
                }
                if ($emails->isNotEmpty()) {
                    $q->orWhereIn('email', $emails->all());
                }
            })
            ->with('nationality:id,name')
            ->orderByDesc('created_at')
            ->get();

        $byUserId = $registrations->groupBy('user_id');
        $byEmail = $registrations->groupBy(fn (GroupRegistration $r) => mb_strtolower(trim((string) $r->email)));

        $map = [];
        foreach ($requests as $request) {
            $reg = null;
            if ($request->student_id && $byUserId->has($request->student_id)) {
                $reg = $byUserId->get($request->student_id)->first();
            }
            if (! $reg && $request->student?->email) {
                $emailKey = mb_strtolower(trim($request->student->email));
                if ($byEmail->has($emailKey)) {
                    $reg = $byEmail->get($emailKey)->first();
                }
            }
            $map[(int) $request->id] = $reg;
        }

        return $map;
    }
}
