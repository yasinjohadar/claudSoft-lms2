@extends('admin.layouts.master')

@section('page-title')
    إرسال تذكير جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إرسال تذكير جماعي جديد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">التذكيرات</a></li>
                        <li class="breadcrumb-item active">إرسال جديد</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.reminders.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-2"></i>العودة للقائمة
                </a>
            </div>
        </div>

        <!-- Create Form -->
        <form action="{{ route('admin.reminders.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">معلومات التذكير</div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Title -->
                                <div class="col-12">
                                    <label class="form-label">عنوان التذكير <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}" required placeholder="مثال: موعد الامتحان النهائي">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Message -->
                                <div class="col-12">
                                    <label class="form-label">محتوى الرسالة <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                              rows="6" required placeholder="اكتب محتوى التذكير هنا...">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">يمكنك كتابة رسالة تفصيلية للطلاب</small>
                                </div>

                                <!-- Target Type & ID -->
                                <div class="col-md-6">
                                    <label class="form-label">نوع الهدف <span class="text-danger">*</span></label>
                                    <select name="target_type" id="targetType" class="form-select @error('target_type') is-invalid @enderror" required>
                                        <option value="">اختر نوع الهدف</option>
                                        <option value="course" {{ old('target_type') == 'course' ? 'selected' : '' }}>كورس تدريبي</option>
                                        <option value="group" {{ old('target_type') == 'group' ? 'selected' : '' }}>مجموعة</option>
                                        <option value="training_camp" {{ old('target_type') == 'training_camp' ? 'selected' : '' }}>معسكر تدريبي</option>
                                    </select>
                                    @error('target_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">اختر الهدف <span class="text-danger">*</span></label>
                                    <select name="target_id" id="targetId" class="form-select @error('target_id') is-invalid @enderror" required>
                                        <option value="">اختر الهدف أولاً</option>
                                    </select>
                                    @error('target_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Reminder Type & Priority -->
                                <div class="col-md-6">
                                    <label class="form-label">نوع التذكير <span class="text-danger">*</span></label>
                                    <select name="reminder_type" class="form-select @error('reminder_type') is-invalid @enderror" required>
                                        @foreach(\App\Models\GroupReminder::getReminderTypes() as $key => $type)
                                            <option value="{{ $key }}" {{ old('reminder_type') == $key ? 'selected' : '' }}>
                                                {{ $type['icon'] }} {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('reminder_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">الأولوية <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                        @foreach(\App\Models\GroupReminder::getPriorities() as $key => $priority)
                                            <option value="{{ $key }}" {{ old('priority', 'medium') == $key ? 'selected' : '' }}>
                                                {{ $priority['icon'] }} {{ $priority['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remind At -->
                                <div class="col-12">
                                    <label class="form-label">موعد الإرسال (اختياري)</label>
                                    <input type="datetime-local" name="remind_at" class="form-control @error('remind_at') is-invalid @enderror"
                                           value="{{ old('remind_at') }}">
                                    @error('remind_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">إذا تركت هذا الحقل فارغاً، سيتم إرسال التذكير فوراً</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Sidebar -->
                <div class="col-lg-4">
                    <!-- Sending Methods -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">طرق الإرسال</div>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="send_email" class="form-check-input" id="sendEmail"
                                       {{ old('send_email', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sendEmail">
                                    <i class="ri-mail-line text-primary me-2"></i>إرسال عبر البريد الإلكتروني
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="send_notification" class="form-check-input" id="sendNotification"
                                       {{ old('send_notification', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="sendNotification">
                                    <i class="ri-notification-line text-info me-2"></i>إرسال إشعار داخلي
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Recipients -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">المستلمون</div>
                        </div>
                        <div class="card-body">
                            <div id="recipientsPreview">
                                <div class="text-center text-muted py-3">
                                    <i class="ri-group-line fs-3"></i>
                                    <p class="mb-0 mt-2">اختر الهدف لمعرفة عدد المستلمين</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="ri-send-plane-fill me-2"></i>إرسال التذكير
                            </button>
                            <a href="{{ route('admin.reminders.index') }}" class="btn btn-light w-100">
                                <i class="ri-close-line me-2"></i>إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
// Load targets based on type
document.getElementById('targetType').addEventListener('change', function() {
    const type = this.value;
    const targetSelect = document.getElementById('targetId');

    if (!type) {
        targetSelect.innerHTML = '<option value="">اختر الهدف أولاً</option>';
        return;
    }

    // For now, just load courses (you can extend this later)
    if (type === 'course') {
        fetch('/api/admin/courses')
            .then(response => response.json())
            .then(data => {
                targetSelect.innerHTML = '<option value="">اختر الكورس...</option>';
                data.forEach(item => {
                    targetSelect.innerHTML += `<option value="${item.id}">${item.title}</option>`;
                });
            })
            .catch(error => {
                console.error('Error loading targets:', error);
                targetSelect.innerHTML = '<option value="">حدث خطأ في التحميل</option>';
            });
    }
});

// Load recipients count when target is selected
document.getElementById('targetId').addEventListener('change', function() {
    const targetType = document.getElementById('targetType').value;
    const targetId = this.value;

    if (!targetId || !targetType) {
        document.getElementById('recipientsPreview').innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="ri-group-line fs-3"></i>
                <p class="mb-0 mt-2">اختر الهدف لمعرفة عدد المستلمين</p>
            </div>
        `;
        return;
    }

    // Mock count for now (you can implement actual API later)
    document.getElementById('recipientsPreview').innerHTML = `
        <div class="text-center">
            <div class="display-6 text-primary mb-2">--</div>
            <p class="text-muted mb-0">طالب مسجل</p>
        </div>
    `;
});
</script>
@endsection
