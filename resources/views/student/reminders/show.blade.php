@extends('student.layouts.master')

@section('page-title')
    تفاصيل التذكير
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

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
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.reminders.index') }}">التذكيرات</a></li>
                        <li class="breadcrumb-item active">تفاصيل</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.reminders.index') }}" class="btn btn-light">
                    <i class="ri-arrow-right-line me-2"></i>العودة للتذكيرات
                </a>
            </div>
        </div>

        <!-- Reminder Card -->
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
            <!-- Date & Sender Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="ri-calendar-line fs-5 text-muted"></i>
                        <div>
                            <small class="text-muted d-block">تاريخ الإرسال</small>
                            <strong>{{ $reminder->sent_at ? $reminder->sent_at->format('Y/m/d H:i') : 'لم يرسل بعد' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="ri-user-line fs-5 text-muted"></i>
                        <div>
                            <small class="text-muted d-block">المرسل</small>
                            <strong>{{ $reminder->creator->name }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Content -->
            <div class="alert alert-light border">
                <h5 class="mb-3">📄 محتوى الرسالة:</h5>
                <div class="message-content" style="white-space: pre-wrap;">{{ $reminder->message }}</div>
            </div>

            <!-- Target Info -->
            <div class="card custom-card bg-light mt-4">
                <div class="card-body">
                    <h6 class="mb-3">🎯 معلومات الاستهداف</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">نوع الهدف</small>
                            <strong>
                                @if($reminder->target_type === 'App\Models\Course')
                                    كورس تدريبي
                                @elseif($reminder->target_type === 'App\Models\Group')
                                    مجموعة
                                @else
                                    معسكر تدريبي
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">اسم الهدف</small>
                            <strong>{{ $reminder->target->title ?? $reminder->target->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">عدد المستلمين</small>
                            <strong>{{ $reminder->recipients_count }} طالب</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Methods -->
            <div class="mt-4">
                <h6 class="mb-3">📬 طرق الإرسال</h6>
                <div class="d-flex gap-3">
                    @if($reminder->send_email)
                        <span class="badge bg-success fs-6">
                            <i class="ri-mail-line me-2"></i>بريد إلكتروني
                        </span>
                    @endif
                    @if($reminder->send_notification)
                        <span class="badge bg-info fs-6">
                            <i class="ri-notification-line me-2"></i>إشعار داخلي
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-footer text-muted">
            <small>
                <i class="ri-time-line me-1"></i>
                تم الإنشاء: {{ $reminder->created_at->format('Y/m/d H:i') }}
                ({{ $reminder->created_at->diffForHumans() }})
            </small>
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
