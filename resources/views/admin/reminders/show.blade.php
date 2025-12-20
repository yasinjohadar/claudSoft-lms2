@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التذكير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        @php
            $types = \App\Models\GroupReminder::getReminderTypes();
            $priorities = \App\Models\GroupReminder::getPriorities();
            $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
            $priorityInfo = $priorities[$reminder->priority] ?? $priorities['medium'];
        @endphp

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل التذكير</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">التذكيرات</a></li>
                        <li class="breadcrumb-item active">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.reminders.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-2"></i>العودة للقائمة
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Reminder Details Card -->
                <div class="card custom-card border-{{ $typeInfo['color'] }}">
                <div class="card-header bg-{{ $typeInfo['color'] }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-1">{{ $typeInfo['icon'] }}</span>
                            <div>
                                <h4 class="mb-0 text-white">{{ $reminder->title }}</h4>
                                <span class="badge bg-white text-{{ $typeInfo['color'] }} mt-2">
                                    {{ $typeInfo['name'] }}
                                </span>
                            </div>
                        </div>
                        <span class="badge bg-{{ $priorityInfo['color'] }} fs-6">
                            {{ $priorityInfo['icon'] }} {{ $priorityInfo['name'] }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Message Content -->
                    <div class="alert alert-light border mb-4">
                        <h5 class="mb-3">📄 محتوى الرسالة:</h5>
                        <div class="message-content" style="white-space: pre-wrap;">{{ $reminder->message }}</div>
                    </div>

                    <!-- Target Info -->
                    <div class="card custom-card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">🎯 معلومات الاستهداف</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">نوع الهدف</small>
                                    <strong>
                                        @if($reminder->target_type === 'App\Models\Course')
                                            <i class="ri-book-line me-1"></i>كورس تدريبي
                                        @elseif($reminder->target_type === 'App\Models\Group')
                                            <i class="ri-group-line me-1"></i>مجموعة
                                        @else
                                            <i class="ri-tent-line me-1"></i>معسكر تدريبي
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">اسم الهدف</small>
                                    <strong>{{ $reminder->target->title ?? $reminder->target->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">الحالة</h6>
                </div>
                <div class="card-body text-center">
                    @if($reminder->is_sent)
                        <div class="display-6 text-success mb-2">
                            <i class="ri-checkbox-circle-fill"></i>
                        </div>
                        <h5 class="text-success">تم الإرسال</h5>
                        <p class="text-muted mb-0">{{ $reminder->sent_at->format('Y/m/d H:i') }}</p>
                    @else
                        <div class="display-6 text-warning mb-2">
                            <i class="ri-time-fill"></i>
                        </div>
                        <h5 class="text-warning">قيد الانتظار</h5>
                        @if($reminder->remind_at)
                            <p class="text-muted mb-0">موعد الإرسال: {{ $reminder->remind_at->format('Y/m/d H:i') }}</p>
                        @endif
                    @endif
                </div>
                </div>

                <!-- Recipients Card -->
                <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">المستلمون</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 text-primary">{{ $reminder->recipients_count }}</div>
                        <p class="text-muted mb-0">طالب</p>
                    </div>
                    @if($reminder->read_count > 0)
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ ($reminder->read_count / $reminder->recipients_count) * 100 }}%">
                                {{ round(($reminder->read_count / $reminder->recipients_count) * 100) }}%
                            </div>
                        </div>
                        <small class="text-muted">{{ $reminder->read_count }} طالب قرأوا التذكير</small>
                    @endif
                </div>
                </div>

                <!-- Sending Methods Card -->
                <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">طرق الإرسال</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        @if($reminder->send_email)
                            <div class="d-flex align-items-center">
                                <i class="ri-mail-line text-success fs-5 me-2"></i>
                                <span>بريد إلكتروني</span>
                            </div>
                        @endif
                        @if($reminder->send_notification)
                            <div class="d-flex align-items-center">
                                <i class="ri-notification-line text-info fs-5 me-2"></i>
                                <span>إشعار داخلي</span>
                            </div>
                        @endif
                    </div>
                </div>
                </div>

                <!-- Creator Info Card -->
                <div class="card custom-card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">معلومات المنشئ</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="ri-user-line text-muted"></i>
                        <div>
                            <small class="text-muted d-block">المنشئ</small>
                            <strong>{{ $reminder->creator->name }}</strong>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-calendar-line text-muted"></i>
                        <div>
                            <small class="text-muted d-block">تاريخ الإنشاء</small>
                            <strong>{{ $reminder->created_at->format('Y/m/d H:i') }}</strong>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Actions Card -->
                <div class="card custom-card">
                <div class="card-body">
                    @if(!$reminder->is_sent)
                        <form action="{{ route('admin.reminders.send', $reminder) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('هل تريد إرسال هذا التذكير الآن؟')">
                                <i class="ri-send-plane-fill me-2"></i>إرسال الآن
                            </button>
                        </form>
                        <a href="{{ route('admin.reminders.edit', $reminder) }}" class="btn btn-warning w-100 mb-2">
                            <i class="ri-edit-line me-2"></i>تعديل
                        </a>
                    @endif
                    <form action="{{ route('admin.reminders.destroy', $reminder) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('هل تريد حذف هذا التذكير؟')">
                            <i class="ri-delete-bin-line me-2"></i>حذف
                        </button>
                    </form>
                </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.message-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}
</style>
@endsection
