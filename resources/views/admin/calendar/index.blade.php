@extends('admin.layouts.master')

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
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقويم والمواعيد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">التقويم</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.reminders.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-2"></i>إضافة تذكير جديد
                </a>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="card custom-card mb-4">
            <div class="card-body">
                <h6 class="mb-3">دليل الألوان:</h6>
                <div class="d-flex gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #3b82f6; border-radius: 4px;"></div>
                        <span>واجب / إعلان</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #ef4444; border-radius: 4px;"></div>
                        <span>امتحان</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #f59e0b; border-radius: 4px;"></div>
                        <span>موعد نهائي</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #10b981; border-radius: 4px;"></div>
                        <span>حدث</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 20px; height: 20px; background-color: #6b7280; border-radius: 4px;"></div>
                        <span>اجتماع</span>
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
                <a href="#" id="eventDetailsLink" class="btn btn-primary">عرض التفاصيل الكاملة</a>
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

    console.log('Initializing calendar...');

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
        events: {
            url: '{{ route("admin.calendar.events") }}',
            method: 'GET',
            extraParams: function() {
                return {
                    _token: '{{ csrf_token() }}'
                };
            },
            failure: function(error) {
                console.error('Calendar error:', error);
                alert('حدث خطأ في تحميل الأحداث');
            }
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
            html += '<div class="col-md-6"><strong>النوع:</strong> ' + (props.type || '') + '</div>';
            html += '<div class="col-md-6"><strong>الأولوية:</strong> ' + (props.priority || '') + '</div>';
            html += '<div class="col-md-6"><strong>التاريخ:</strong> ' + event.start.toLocaleString('ar-EG') + '</div>';
            html += '<div class="col-md-6"><strong>الحالة:</strong> ' + (props.status || '') + '</div>';
            html += '<div class="col-md-6"><strong>المستلمون:</strong> ' + (props.recipients || 0) + ' طالب</div>';
            html += '<div class="col-md-6"><strong>المنشئ:</strong> ' + (props.creator || '') + '</div>';
            html += '<div class="col-md-6"><strong>الهدف:</strong> ' + (props.target || '') + '</div>';
            html += '<div class="col-12"><strong>الرسالة:</strong><p class="mt-2">' + (props.message || '') + '</p></div>';
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
});
</script>
@endsection
