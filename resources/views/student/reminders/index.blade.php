@extends('student.layouts.master')

@section('page-title')
    التذكيرات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التذكيرات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">التذكيرات</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Reminders List -->
        <div class="row">
            @forelse($reminders as $reminder)
                <div class="col-md-6 col-lg-4 mb-4">
                    @php
                        $types = \App\Models\GroupReminder::getReminderTypes();
                        $priorities = \App\Models\GroupReminder::getPriorities();
                        $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
                        $priorityInfo = $priorities[$reminder->priority] ?? $priorities['medium'];
                    @endphp

                    <div class="card custom-card h-100 border-{{ $typeInfo['color'] }}">
                    <div class="card-body">
                        <!-- Header with Type Icon -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="fs-2">{{ $typeInfo['icon'] }}</span>
                                <h5 class="card-title mt-2 mb-1">{{ $reminder->title }}</h5>
                            </div>
                            <span class="badge bg-{{ $priorityInfo['color'] }}">
                                {{ $priorityInfo['icon'] }} {{ $priorityInfo['name'] }}
                            </span>
                        </div>

                        <!-- Type & Date Info -->
                        <div class="mb-3">
                            <span class="badge bg-{{ $typeInfo['color'] }} mb-2">
                                {{ $typeInfo['name'] }}
                            </span>
                            <br>
                            <small class="text-muted">
                                <i class="ri-calendar-line me-1"></i>
                                {{ $reminder->sent_at ? $reminder->sent_at->format('Y/m/d H:i') : 'قريباً' }}
                            </small>
                        </div>

                        <!-- Message Preview -->
                        <p class="card-text text-muted">
                            {{ Str::limit($reminder->message, 150) }}
                        </p>

                        <!-- Sender Info -->
                        <div class="border-top pt-2 mt-3">
                            <small class="text-muted">
                                <i class="ri-user-line me-1"></i>
                                من: {{ $reminder->creator->name }}
                            </small>
                        </div>

                        <!-- View Details Button -->
                        <a href="{{ route('student.reminders.show', $reminder) }}" class="btn btn-sm btn-outline-{{ $typeInfo['color'] }} w-100 mt-3">
                            <i class="ri-eye-line me-2"></i>عرض التفاصيل
                        </a>
                    </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="ri-notification-off-line fs-3"></i>
                    <p class="mb-0 mt-2">لا توجد تذكيرات حالياً</p>
                </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $reminders->links() }}
        </div>

    </div>
</div>
@endsection
