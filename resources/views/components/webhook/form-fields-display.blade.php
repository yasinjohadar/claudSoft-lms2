@props(['formData', 'submissionType' => null])

@php
    // تصنيف الحقول حسب الأهمية
    $priorityFields = [
        'name', 'full_name', 'student_name', 'الاسم', 'اسم الطالب',
        'email', 'student_email', 'البريد الإلكتروني', 'البريد',
        'phone', 'mobile', 'telephone', 'الهاتف', 'الجوال', 'رقم الهاتف',
        'course', 'course_name', 'course_title', 'الكورس', 'اسم الكورس',
    ];

    $contactFields = ['subject', 'message', 'الموضوع', 'الرسالة'];
    $reviewFields = ['rating', 'review', 'review_title', 'التقييم', 'المراجعة', 'عنوان المراجعة'];
    $paymentFields = ['amount', 'price', 'payment_method', 'المبلغ', 'السعر', 'طريقة الدفع'];

    // فرز الحقول
    $sortedData = [];
    $remainingData = $formData ?? [];

    // 1. إضافة الحقول ذات الأولوية أولاً
    foreach ($priorityFields as $field) {
        foreach ($remainingData as $key => $value) {
            if (stripos($key, $field) !== false || strtolower($key) === strtolower($field)) {
                $sortedData[$key] = $value;
                unset($remainingData[$key]);
            }
        }
    }

    // 2. إضافة الحقول الخاصة بالنوع
    $typeSpecificFields = [];
    switch ($submissionType) {
        case 'contact':
            $typeSpecificFields = $contactFields;
            break;
        case 'review':
            $typeSpecificFields = $reviewFields;
            break;
        case 'payment':
            $typeSpecificFields = $paymentFields;
            break;
    }

    foreach ($typeSpecificFields as $field) {
        foreach ($remainingData as $key => $value) {
            if (stripos($key, $field) !== false || strtolower($key) === strtolower($field)) {
                $sortedData[$key] = $value;
                unset($remainingData[$key]);
            }
        }
    }

    // 3. إضافة باقي الحقول
    $sortedData = array_merge($sortedData, $remainingData);

    // دالة للحصول على أيقونة مناسبة للحقل
    function getFieldIcon($key) {
        $key = strtolower($key);
        if (strpos($key, 'name') !== false || strpos($key, 'اسم') !== false) {
            return 'fa-user';
        } elseif (strpos($key, 'email') !== false || strpos($key, 'بريد') !== false) {
            return 'fa-envelope';
        } elseif (strpos($key, 'phone') !== false || strpos($key, 'هاتف') !== false || strpos($key, 'mobile') !== false) {
            return 'fa-phone';
        } elseif (strpos($key, 'course') !== false || strpos($key, 'كورس') !== false) {
            return 'fa-book';
        } elseif (strpos($key, 'message') !== false || strpos($key, 'رسالة') !== false) {
            return 'fa-comment';
        } elseif (strpos($key, 'subject') !== false || strpos($key, 'موضوع') !== false) {
            return 'fa-heading';
        } elseif (strpos($key, 'rating') !== false || strpos($key, 'تقييم') !== false) {
            return 'fa-star';
        } elseif (strpos($key, 'price') !== false || strpos($key, 'amount') !== false || strpos($key, 'مبلغ') !== false) {
            return 'fa-dollar-sign';
        } elseif (strpos($key, 'date') !== false || strpos($key, 'تاريخ') !== false) {
            return 'fa-calendar';
        } elseif (strpos($key, 'time') !== false || strpos($key, 'وقت') !== false) {
            return 'fa-clock';
        } elseif (strpos($key, 'address') !== false || strpos($key, 'عنوان') !== false) {
            return 'fa-map-marker-alt';
        } elseif (strpos($key, 'url') !== false || strpos($key, 'link') !== false || strpos($key, 'رابط') !== false) {
            return 'fa-link';
        } elseif (strpos($key, 'file') !== false || strpos($key, 'ملف') !== false) {
            return 'fa-file';
        } else {
            return 'fa-info-circle';
        }
    }

    // دالة لتنسيق اسم الحقل
    function formatFieldName($key) {
        // إزالة الأرقام والشرطات السفلية
        $formatted = preg_replace('/[0-9]+/', '', $key);
        $formatted = str_replace('_', ' ', $formatted);
        $formatted = str_replace('-', ' ', $formatted);

        // تحويل أول حرف إلى uppercase
        return ucfirst($formatted);
    }

    // دالة لتحديد نوع القيمة وتنسيقها
    function formatFieldValue($value) {
        if (is_bool($value)) {
            return $value ? '<span class="badge badge-success">نعم</span>' : '<span class="badge badge-secondary">لا</span>';
        } elseif (is_numeric($value) && strpos($value, '.') !== false) {
            return number_format((float)$value, 2);
        } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '<a href="mailto:' . $value . '" class="text-primary">' . $value . '</a>';
        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
            return '<a href="' . $value . '" target="_blank" class="text-primary">' . Str::limit($value, 50) . ' <i class="fas fa-external-link-alt fa-xs"></i></a>';
        } elseif (strlen($value) > 200) {
            return '<div class="expandable-text">' . e(substr($value, 0, 200)) . '... <a href="#" class="expand-link" onclick="event.preventDefault(); this.parentElement.classList.toggle(\'expanded\');">عرض المزيد</a><span class="hidden-text" style="display:none;">' . e(substr($value, 200)) . '</span></div>';
        } else {
            return e($value);
        }
    }

    // تحديد ما إذا كان الحقل مهماً
    function isImportantField($key) {
        $importantKeywords = ['name', 'email', 'phone', 'course', 'الاسم', 'البريد', 'الهاتف', 'الكورس'];
        foreach ($importantKeywords as $keyword) {
            if (stripos($key, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
@endphp

<div class="form-fields-display">
    @if(!empty($sortedData))
        <div class="row">
            @foreach($sortedData as $key => $value)
                @php
                    $isImportant = isImportantField($key);
                    $icon = getFieldIcon($key);
                    $displayName = formatFieldName($key);
                @endphp

                <div class="col-md-{{ $isImportant ? '6' : '12' }} mb-3">
                    <div class="field-card {{ $isImportant ? 'important-field' : '' }} p-3 border rounded h-100">
                        <div class="field-header d-flex align-items-center mb-2">
                            <i class="fas {{ $icon }} text-primary mr-2"></i>
                            <strong class="field-label">{{ $displayName }}</strong>
                            @if($isImportant)
                                <span class="badge badge-primary badge-sm ml-auto">مهم</span>
                            @endif
                        </div>
                        <div class="field-value">
                            @if(is_array($value))
                                @if(isset($value['name']) && isset($value['value']))
                                    {{-- WPForms field structure --}}
                                    <div class="wpforms-field">
                                        <div class="text-muted small mb-1">{{ $value['name'] ?? $key }}</div>
                                        <div>{!! formatFieldValue($value['value'] ?? '-') !!}</div>
                                    </div>
                                @else
                                    {{-- Regular array --}}
                                    <div class="array-display">
                                        <details>
                                            <summary class="cursor-pointer text-primary">عرض البيانات ({{ count($value) }} عنصر)</summary>
                                            <pre class="bg-light p-2 rounded mt-2 mb-0 small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    </div>
                                @endif
                            @elseif(empty($value))
                                <span class="text-muted">-</span>
                            @else
                                {!! formatFieldValue($value) !!}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> لا توجد بيانات إضافية
        </div>
    @endif
</div>

@push('styles')
<style>
    .form-fields-display .field-card {
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .form-fields-display .field-card:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .form-fields-display .important-field {
        border-left: 4px solid #4e73df !important;
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    }

    .form-fields-display .field-header {
        font-size: 0.9rem;
    }

    .form-fields-display .field-label {
        color: #5a5c69;
        font-size: 0.95rem;
    }

    .form-fields-display .field-value {
        color: #3a3b45;
        font-size: 1rem;
        word-break: break-word;
    }

    .form-fields-display .expandable-text.expanded .hidden-text {
        display: inline !important;
    }

    .form-fields-display .expandable-text .expand-link {
        color: #4e73df;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .form-fields-display .expandable-text .expand-link:hover {
        text-decoration: underline;
    }

    .form-fields-display .badge-sm {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    .form-fields-display details summary {
        list-style: none;
    }

    .form-fields-display details summary::-webkit-details-marker {
        display: none;
    }

    .form-fields-display details[open] summary::after {
        content: " ▲";
    }

    .form-fields-display details:not([open]) summary::after {
        content: " ▼";
    }
</style>
@endpush
