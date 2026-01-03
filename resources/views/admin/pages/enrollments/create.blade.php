@extends('admin.layouts.master')

@section('page-title')
    تسجيل طالب - {{ $course->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسجيل طالب جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">تسجيل طالب</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <div class="row">
                <div class="col-md-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">اختر الطالب</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('courses.enrollments.enroll-individual', $course->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">الطلاب <span class="text-danger">*</span></label>
                                    @if($students->count() > 0)
                                        <select name="student_ids[]" id="studentSelect" class="form-select @error('student_ids') is-invalid @enderror" multiple required>
                                            @foreach($students as $student)
                                                @php
                                                    $displayName = $student->name;
                                                    if ($student->name_ar) {
                                                        $displayName .= ' (' . $student->name_ar . ')';
                                                    }
                                                    $displayName .= ' - ' . $student->email;
                                                @endphp
                                                <option value="{{ $student->id }}" 
                                                        {{ (is_array(old('student_ids')) && in_array($student->id, old('student_ids'))) ? 'selected' : '' }}>
                                                    {{ $displayName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_ids')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            <i class="fas fa-search me-1"></i>
                                            ابدأ بالكتابة للبحث عن الطلاب بالاسم (عربي/إنجليزي) أو البريد الإلكتروني. يمكنك اختيار عدة طلاب
                                        </small>
                                        <div id="selectedCount" class="mt-2 text-primary" style="display: none;">
                                            <i class="fas fa-users me-1"></i>
                                            <span id="selectedCountText">0</span> طالب محدد
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا يوجد طلاب متاحين للتسجيل. جميع الطلاب مسجلون بالفعل في هذا الكورس.
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="send_notification" id="sendNotification" checked>
                                        <label class="form-check-label" for="sendNotification">
                                            إرسال إشعار بالبريد الإلكتروني
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-light">
                                        <i class="fas fa-arrow-right me-2"></i>رجوع
                                    </a>
                                    @if($students->count() > 0)
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-user-plus me-2"></i>
                                            <span id="submitBtnText">تسجيل الطلاب</span>
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الكورس</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $course->title }}</h5>
                            <p class="text-muted">{{ $course->short_description }}</p>
                            <hr>
                            <div class="mb-2">
                                <strong>المسجلين حالياً:</strong> {{ $course->enrollments_count ?? 0 }}
                            </div>
                            @if($course->max_students)
                                <div class="mb-2">
                                    <strong>الحد الأقصى:</strong> {{ $course->max_students }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Choices.js for searchable multi-select
        const studentSelect = document.getElementById('studentSelect');
        let choicesInstance = null;
        
        if (studentSelect) {
            // Wait for Choices.js to be available
            const initChoices = function() {
                if (typeof Choices !== 'undefined' || typeof window.Choices !== 'undefined') {
                    const ChoicesClass = typeof Choices !== 'undefined' ? Choices : window.Choices;
                    
                    // Destroy existing instance if any
                    if (studentSelect._choicesjs) {
                        studentSelect._choicesjs.destroy();
                    }
                    
                    choicesInstance = new ChoicesClass(studentSelect, {
                        removeItemButton: true,
                        searchEnabled: true,
                        searchChoices: true,
                        placeholder: true,
                        placeholderValue: 'اختر طالب أو أكثر',
                        searchPlaceholderValue: 'ابحث بالاسم (عربي/إنجليزي) أو البريد الإلكتروني...',
                        itemSelectText: '',
                        shouldSort: false,
                        allowHTML: true,
                        fuseOptions: {
                            threshold: 0.4,
                            minMatchCharLength: 1,
                            includeScore: false
                        },
                        classNames: {
                            containerOuter: 'choices',
                            containerInner: 'choices__inner',
                            input: 'choices__input',
                            inputCloned: 'choices__input--cloned',
                            list: 'choices__list',
                            listItems: 'choices__list--multiple',
                            listSingle: 'choices__list--single',
                            listDropdown: 'choices__list--dropdown',
                            item: 'choices__item',
                            itemSelectable: 'choices__item--selectable',
                            itemDisabled: 'choices__item--disabled',
                            itemChoice: 'choices__item--choice',
                            placeholder: 'choices__placeholder',
                            group: 'choices__group',
                            groupHeading: 'choices__heading',
                            button: 'choices__button',
                            activeState: 'is-active',
                            focusState: 'is-focused',
                            openState: 'is-open',
                            disabledState: 'is-disabled',
                            highlightedState: 'is-highlighted',
                            selectedState: 'is-selected',
                            flippedState: 'is-flipped',
                            loadingState: 'is-loading',
                            noResults: 'has-no-results',
                            noChoices: 'has-no-choices'
                        }
                    });

                    // Update selected count when choices change
                    studentSelect.addEventListener('change', function() {
                        updateSelectedCount();
                    });

                    // Also listen to Choices.js events
                    studentSelect.addEventListener('addItem', function() {
                        updateSelectedCount();
                    });

                    studentSelect.addEventListener('removeItem', function() {
                        updateSelectedCount();
                    });

                    // Initial count update
                    updateSelectedCount();
                } else {
                    // Retry after a short delay if Choices.js is not loaded yet
                    setTimeout(initChoices, 100);
                }
            };
            
            initChoices();
        }

        // Update selected count display
        function updateSelectedCount() {
            const selectedCountDiv = document.getElementById('selectedCount');
            const selectedCountText = document.getElementById('selectedCountText');
            const submitBtnText = document.getElementById('submitBtnText');
            
            if (studentSelect) {
                const selectedOptions = Array.from(studentSelect.selectedOptions);
                const count = selectedOptions.length;
                
                if (count > 0) {
                    selectedCountDiv.style.display = 'block';
                    selectedCountText.textContent = count;
                    
                    if (submitBtnText) {
                        if (count === 1) {
                            submitBtnText.textContent = 'تسجيل طالب واحد';
                        } else {
                            submitBtnText.textContent = `تسجيل ${count} طلاب`;
                        }
                    }
                } else {
                    selectedCountDiv.style.display = 'none';
                    if (submitBtnText) {
                        submitBtnText.textContent = 'تسجيل الطلاب';
                    }
                }
            }
        }

        // Form validation
        const form = document.querySelector('form[action*="enroll-individual"]');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const selectedOptions = studentSelect ? Array.from(studentSelect.selectedOptions) : [];
                
                if (!studentSelect || selectedOptions.length === 0) {
                    e.preventDefault();
                    alert('يرجى اختيار طالب واحد على الأقل');
                    if (studentSelect) {
                        studentSelect.focus();
                    }
                    return false;
                }
            });
        }
    });
</script>
@stop
