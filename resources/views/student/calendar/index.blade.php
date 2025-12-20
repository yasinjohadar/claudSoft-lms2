@extends('student.layouts.master')

@section('page-title')
    التقويم والمواعيد
@stop

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<style>
    .fc {
        direction: rtl;
        text-align: right;
    }

    .fc-toolbar-title {
        font-size: 1.5em !important;
    }

    .fc-button {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }

    .fc-button:hover {
        background-color: #2563eb !important;
    }

    .fc-event {
        cursor: pointer;
    }

    /* تحسين شكل كروت الإحصائيات */
    .calendar-stat-card {
        border: none;
        border-radius: 18px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        position: relative;
    }

    .calendar-stat-card .card-body {
        position: relative;
        z-index: 1;
    }

    .calendar-stat-card::before {
        content: "";
        position: absolute;
        inset-inline-end: -40px;
        inset-block-end: -40px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(15, 23, 42, 0.08);
        opacity: 0.3;
    }

    .calendar-stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(15, 23, 42, 0.12);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }

    .calendar-stat-icon i {
        font-size: 26px;
    }

    .calendar-stat-value {
        font-size: 32px;
        font-weight: 800;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .calendar-stat-label {
        font-size: 14px;
        opacity: 0.9;
    }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقويم والمواعيد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">التقويم</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.notes.index') }}" class="btn btn-primary">
                    <i class="ri-add-line me-2"></i>إضافة ملاحظة جديدة
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card custom-card calendar-stat-card bg-primary text-white">
                    <div class="card-body text-center p-4">
                        <div class="calendar-stat-icon mx-auto mb-3">
                            <i class="ri-sticky-note-line"></i>
                        </div>
                        <div class="calendar-stat-value" id="notesCount">0</div>
                        <div class="calendar-stat-label">ملاحظات شخصية</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card calendar-stat-card bg-success text-white">
                    <div class="card-body text-center p-4">
                        <div class="calendar-stat-icon mx-auto mb-3">
                            <i class="ri-notification-badge-line"></i>
                        </div>
                        <div class="calendar-stat-value" id="remindersCount">0</div>
                        <div class="calendar-stat-label">تذكيرات</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card calendar-stat-card bg-info text-white">
                    <div class="card-body text-center p-4">
                        <div class="calendar-stat-icon mx-auto mb-3">
                            <i class="ri-calendar-check-line"></i>
                        </div>
                        <div class="calendar-stat-value" id="upcomingCount">0</div>
                        <div class="calendar-stat-label">مواعيد قادمة</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <h6 class="mb-3">دليل الألوان:</h6>
                <div class="d-flex gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #3b82f6; border-radius: 4px;"></div>
                        <span>ملاحظات شخصية</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #ef4444; border-radius: 4px;"></div>
                        <span>تذكيرات مهمة</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #f59e0b; border-radius: 4px;"></div>
                        <span>مواعيد نهائية</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #10b981; border-radius: 4px;"></div>
                        <span>أحداث</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="card custom-card">
            <div class="card-body">
                <div id="calendar" style="min-height: 600px;"></div>
            </div>
        </div>

    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">تفاصيل الموعد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <a href="#" id="eventDetailsLink" class="btn btn-primary">عرض التفاصيل</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/ar.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var allEvents = [];

    console.log('Initializing student calendar...');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'اليوم',
            month: 'شهر',
            week: 'أسبوع',
            list: 'قائمة'
        },
        events: function(info, successCallback, failureCallback) {
            console.log('Fetching events from:', info.startStr, 'to:', info.endStr);
            fetch('{{ route("student.calendar.events") }}?start=' + info.startStr + '&end=' + info.endStr, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(function(response) {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(function(data) {
                    console.log('Events loaded:', data.length);
                    allEvents = data;
                    updateStats(data);
                    successCallback(data);
                })
                .catch(function(error) {
                    console.error('Error loading events:', error);
                    alert('حدث خطأ في تحميل الأحداث');
                    successCallback([]);
                });
        },
        loading: function(isLoading) {
            console.log('Calendar loading:', isLoading);
        },
        eventDidMount: function(info) {
            console.log('Event mounted:', info.event.title);
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();

            var event = info.event;
            var props = event.extendedProps;

            document.getElementById('eventModalTitle').textContent = event.title;

            var html = '<div class="row g-3">';

            if (event.id.startsWith('note-')) {
                // Personal note
                html += '<div class="col-md-6"><strong>النوع:</strong> ' + props.type + '</div>';
                html += '<div class="col-md-6"><strong>التصنيف:</strong> ' + props.category + '</div>';
                html += '<div class="col-md-6"><strong>التاريخ:</strong> ' + new Date(event.start).toLocaleString('ar-EG') + '</div>';
                html += '<div class="col-md-6"><strong>الحالة:</strong> ';
                if (props.isPinned) html += '<span class="badge bg-warning">مثبتة</span> ';
                if (props.isFavorite) html += '<span class="badge bg-danger">مفضلة</span>';
                html += '</div>';
                html += '<div class="col-12"><strong>المحتوى:</strong><p class="mt-2">' + props.content + '</p></div>';
            } else {
                // Group reminder
                html += '<div class="col-md-6"><strong>النوع:</strong> ' + props.type + '</div>';
                html += '<div class="col-md-6"><strong>نوع التذكير:</strong> ' + props.reminderType + '</div>';
                html += '<div class="col-md-6"><strong>الأولوية:</strong> ' + props.priority + '</div>';
                html += '<div class="col-md-6"><strong>التاريخ:</strong> ' + new Date(event.start).toLocaleString('ar-EG') + '</div>';
                html += '<div class="col-md-6"><strong>المرسل:</strong> ' + props.creator + '</div>';
                html += '<div class="col-md-6"><strong>الهدف:</strong> ' + props.target + '</div>';
                html += '<div class="col-12"><strong>الرسالة:</strong><p class="mt-2">' + props.message + '</p></div>';
            }

            html += '</div>';

            document.getElementById('eventModalBody').innerHTML = html;
            document.getElementById('eventDetailsLink').href = event.url;

            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        },
        height: 'auto'
    });

    console.log('Rendering calendar...');
    calendar.render();
    console.log('Calendar rendered successfully');

    function updateStats(events) {
        var now = new Date();
        var notesCount = events.filter(function(e) {
            return e.id && e.id.toString().startsWith('note-');
        }).length;
        var remindersCount = events.filter(function(e) {
            return e.id && e.id.toString().startsWith('reminder-');
        }).length;
        var upcomingCount = events.filter(function(e) {
            return new Date(e.start) > now;
        }).length;

        document.getElementById('notesCount').textContent = notesCount;
        document.getElementById('remindersCount').textContent = remindersCount;
        document.getElementById('upcomingCount').textContent = upcomingCount;
    }
});
</script>
@endsection
