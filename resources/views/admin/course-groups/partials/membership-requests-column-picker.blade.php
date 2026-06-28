@php
    use App\Support\MembershipRequestFormColumns;

    $showWaPicker = !empty($waContext['selected_jid']) && empty($waContext['wa_load_error'] ?? null);
    $tableColumnOptions = [
        'id' => 'رقم الطلب (#)',
        'student' => 'اسم الطالب',
        'other_groups' => 'مجموعات أخرى',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'request_date' => 'تاريخ الطلب',
        'payment_date' => 'موعد تسديد الرسوم',
        'form' => 'عرض الفورم',
        'status' => 'الحالة',
    ];
    if ($showWaPicker) {
        $tableColumnOptions = array_merge(
            array_slice($tableColumnOptions, 0, 5, true),
            ['whatsapp' => 'واتساب'],
            array_slice($tableColumnOptions, 5, null, true)
        );
    }

    $formColumnOptions = collect(MembershipRequestFormColumns::definitions())
        ->mapWithKeys(fn ($def, $key) => [$key => $def['label']])
        ->all();

    $columnDefaults = MembershipRequestFormColumns::defaultVisibility();
@endphp

<div class="dropdown">
    <button type="button"
            class="btn btn-sm btn-light border dropdown-toggle"
            id="membershipColumnsDropdown"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false">
        <i class="fe fe-columns me-1"></i>الأعمدة
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3 membership-columns-picker shadow-lg"
         style="min-width: 280px; max-height: min(70vh, 520px); overflow-y: auto;"
         aria-labelledby="membershipColumnsDropdown">
        <p class="fs-12 text-muted mb-2">اختر الأعمدة التي تريد عرضها في الجدول. يُحفظ اختيارك تلقائياً.</p>

        <p class="fs-11 fw-semibold text-uppercase text-muted mb-2">أعمدة الطلب</p>
        <div class="membership-columns-picker__list mb-3">
            @foreach($tableColumnOptions as $key => $label)
                <div class="form-check mb-2">
                    <input class="form-check-input js-mr-col-toggle" type="checkbox"
                           value="{{ $key }}" id="mr_col_{{ $key }}"
                           @checked(($columnDefaults[$key] ?? true) !== false)>
                    <label class="form-check-label fs-13" for="mr_col_{{ $key }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        <p class="fs-11 fw-semibold text-uppercase text-muted mb-2 border-top pt-2">بيانات الفورم</p>
        <p class="fs-11 text-muted mb-2">حقول التسجيل — للعرض السريع في الجدول دون فتح التفاصيل.</p>
        <div class="membership-columns-picker__list mb-3">
            @foreach($formColumnOptions as $key => $label)
                <div class="form-check mb-2">
                    <input class="form-check-input js-mr-col-toggle" type="checkbox"
                           value="{{ $key }}" id="mr_col_{{ $key }}"
                           @checked(($columnDefaults[$key] ?? false) !== false)>
                    <label class="form-check-label fs-13" for="mr_col_{{ $key }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        <div class="d-flex flex-wrap gap-2 border-top pt-2">
            <button type="button" class="btn btn-sm btn-outline-primary flex-fill" id="membershipColumnsSelectAll">
                تحديد الكل
            </button>
            <button type="button" class="btn btn-sm btn-light border flex-fill" id="membershipColumnsReset">
                الافتراضي
            </button>
        </div>
    </div>
</div>
