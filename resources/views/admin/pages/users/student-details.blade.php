@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الطالب - {{ $user->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الطالب</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item active">{{ $user->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @include('admin.components.alerts')

            <!-- Student Info Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/faces/1.jpg') }}" 
                                         alt="{{ $user->name }}" class="rounded-circle">
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ $user->name }}</h4>
                                    @if($user->name_ar)
                                        <p class="text-muted mb-1">{{ $user->name_ar }}</p>
                                    @endif
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                        @if($user->phone)
                                            | <i class="fas fa-phone me-1"></i>{{ $user->phone }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Groups Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>
                                المجموعات المنتمي إليها ({{ $groupMemberships->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($groupMemberships->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>اسم المجموعة</th>
                                                <th>الدور</th>
                                                <th>تاريخ الانضمام</th>
                                                <th>عدد الكورسات</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupMemberships as $membership)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $membership->group->name }}</strong>
                                                        @if($membership->group->description)
                                                            <br><small class="text-muted">{{ $membership->group->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($membership->role === 'leader')
                                                            <span class="badge bg-warning">قائد</span>
                                                        @else
                                                            <span class="badge bg-secondary">عضو</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $membership->joined_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ $membership->group->courses->count() }} كورس
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#removeGroupModal{{ $membership->group->id }}">
                                                            <i class="fas fa-user-minus me-1"></i>إزالة
                                                        </button>
                                                    </td>
                                                </tr>
                                                @if($membership->group->courses->count() > 0)
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="ms-4">
                                                                <strong class="text-muted">الكورسات في هذه المجموعة:</strong>
                                                                <div class="mt-2">
                                                                    @foreach($membership->group->courses as $course)
                                                                        <span class="badge bg-primary me-2 mb-2">
                                                                            {{ $course->title }}
                                                                            @if($course->code)
                                                                                ({{ $course->code }})
                                                                            @endif
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    الطالب غير منتمي لأي مجموعة حالياً
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add to Group Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-user-plus me-2"></i>
                                إضافة الطالب إلى مجموعة
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($availableGroups->count() > 0)
                                <form action="{{ route('users.add-to-group', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">اختر المجموعة</label>
                                            <select name="group_id" class="form-select" required>
                                                <option value="">-- اختر مجموعة --</option>
                                                @foreach($availableGroups as $group)
                                                    <option value="{{ $group->id }}">
                                                        {{ $group->name }}
                                                        ({{ $group->courses->count() }} كورس)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">الدور</label>
                                            <select name="role" class="form-select">
                                                <option value="member">عضو</option>
                                                <option value="leader">قائد</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-1"></i>إضافة
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    لا توجد مجموعات متاحة لإضافة الطالب إليها
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add to Course Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-book-open me-2"></i>
                                إضافة الطالب إلى كورس
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($availableCourses->count() > 0)
                                <form action="{{ route('users.enroll-course', $user->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">اختر الكورس</label>
                                            <select name="course_id" class="form-select" required>
                                                <option value="">-- اختر كورس --</option>
                                                @foreach($availableCourses as $course)
                                                    <option value="{{ $course->id }}">
                                                        {{ $course->title }}
                                                        @if($course->code)
                                                            ({{ $course->code }})
                                                        @endif
                                                        @if($course->category)
                                                            - {{ $course->category->name }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">حالة التسجيل</label>
                                            <select name="enrollment_status" class="form-select">
                                                <option value="active">نشط</option>
                                                <option value="pending">معلق</option>
                                                <option value="suspended">معلق مؤقتاً</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-plus me-1"></i>إضافة
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    لا توجد كورسات متاحة لإضافة الطالب إليها
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Standalone Courses Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-book me-2"></i>
                                الكورسات المنفصلة ({{ $standaloneCourses->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($standaloneCourses->count() > 0)
                                <div class="row">
                                    @foreach($standaloneCourses as $course)
                                        <div class="col-md-4 mb-3">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $course->title }}</h6>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm" 
                                                                title="إزالة من الكورس"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#removeCourseModal{{ $course->id }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    @if($course->code)
                                                        <p class="text-muted mb-2">
                                                            <small>{{ $course->code }}</small>
                                                        </p>
                                                    @endif
                                                    @if($course->category)
                                                        <span class="badge bg-secondary mb-2">
                                                            {{ $course->category->name }}
                                                        </span>
                                                    @endif
                                                    @php
                                                        $enrollment = $enrollments->where('course_id', $course->id)->first();
                                                    @endphp
                                                    @if($enrollment)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                حالة التسجيل: 
                                                                <span class="badge bg-{{ $enrollment->enrollment_status === 'active' ? 'success' : 'warning' }}">
                                                                    {{ $enrollment->enrollment_status }}
                                                                </span>
                                                            </small>
                                                            <br>
                                                            <small class="text-muted">
                                                                التقدم: {{ $enrollment->completion_percentage }}%
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    لا توجد كورسات منفصلة
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('users.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- Remove Course Modals -->
    @foreach($standaloneCourses as $course)
        <div class="modal fade" id="removeCourseModal{{ $course->id }}" tabindex="-1" aria-labelledby="removeCourseModalLabel{{ $course->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="removeCourseModalLabel{{ $course->id }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            تأكيد إزالة الكورس
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-question-circle fa-3x text-danger mb-3"></i>
                            <h5>هل أنت متأكد من إزالة الطالب من هذا الكورس؟</h5>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>تنبيه:</strong> سيتم إزالة الطالب <strong>{{ $user->name }}</strong> من الكورس:
                            <br>
                            <strong class="mt-2 d-block">{{ $course->title }}</strong>
                            @if($course->code)
                                <small class="text-muted">({{ $course->code }})</small>
                            @endif
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <small>ملاحظة: إذا كان الطالب مسجلاً في هذا الكورس من خلال مجموعة، سيتم منع هذه العملية.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                        <form action="{{ route('users.unenroll-course', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>نعم، إزالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Remove Group Modals -->
    @foreach($groupMemberships as $membership)
        <div class="modal fade" id="removeGroupModal{{ $membership->group->id }}" tabindex="-1" aria-labelledby="removeGroupModalLabel{{ $membership->group->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="removeGroupModalLabel{{ $membership->group->id }}">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            تأكيد إزالة من المجموعة
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-question-circle fa-3x text-danger mb-3"></i>
                            <h5>هل أنت متأكد من إزالة الطالب من هذه المجموعة؟</h5>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>تنبيه:</strong> سيتم إزالة الطالب <strong>{{ $user->name }}</strong> من المجموعة:
                            <br>
                            <strong class="mt-2 d-block">{{ $membership->group->name }}</strong>
                        </div>
                        @if($membership->group->courses->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-book me-2"></i>
                                <strong>الكورسات المرتبطة:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($membership->group->courses as $course)
                                        <li>{{ $course->title }}</li>
                                    @endforeach
                                </ul>
                                <small class="d-block mt-2">سيتم أيضاً إلغاء تسجيل الطالب من هذه الكورسات إذا لم يكن مسجلاً فيها من خلال مجموعات أخرى.</small>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                        <form action="{{ route('users.remove-from-group', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="group_id" value="{{ $membership->group->id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i>نعم، إزالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

