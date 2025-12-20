@extends('admin.layouts.master')

@section('page-title')
    إدارة التذكيرات الجماعية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة التذكيرات الجماعية</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">التذكيرات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('admin.reminders.statistics') }}" class="btn btn-info">
                        <i class="ri-bar-chart-box-line me-2"></i>الإحصائيات
                    </a>
                    <a href="{{ route('admin.reminders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-2"></i>إرسال تذكير جديد
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">نوع التذكير</label>
                            <select class="form-select" id="typeFilter">
                                <option value="">جميع الأنواع</option>
                                @foreach(\App\Models\GroupReminder::getReminderTypes() as $key => $type)
                                    <option value="{{ $key }}">{{ $type['icon'] }} {{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الأولوية</label>
                            <select class="form-select" id="priorityFilter">
                                <option value="">جميع الأولويات</option>
                                @foreach(\App\Models\GroupReminder::getPriorities() as $key => $priority)
                                    <option value="{{ $key }}">{{ $priority['icon'] }} {{ $priority['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">جميع الحالات</option>
                                <option value="sent">مرسلة</option>
                                <option value="pending">قيد الانتظار</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-secondary w-100" onclick="applyFilters()">
                                <i class="ri-filter-3-line me-2"></i>تطبيق الفلتر
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reminders Table -->
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">
                        جميع التذكيرات ({{ $reminders->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>النوع</th>
                                    <th>الأولوية</th>
                                    <th>الهدف</th>
                                    <th>المستلمون</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الإرسال</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reminders as $reminder)
                                    @php
                                        $types = \App\Models\GroupReminder::getReminderTypes();
                                        $priorities = \App\Models\GroupReminder::getPriorities();
                                        $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
                                        $priorityInfo = $priorities[$reminder->priority] ?? $priorities['medium'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fs-4 me-2">{{ $typeInfo['icon'] }}</span>
                                                <div>
                                                    <strong>{{ $reminder->title }}</strong>
                                                    <br>
                                                    <small class="text-muted">بواسطة: {{ $reminder->creator->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $typeInfo['color'] }}">
                                                {{ $typeInfo['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $priorityInfo['color'] }}">
                                                {{ $priorityInfo['icon'] }} {{ $priorityInfo['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @if($reminder->target_type === 'App\Models\Course')
                                                    <i class="ri-book-line me-1"></i>كورس
                                                @elseif($reminder->target_type === 'App\Models\Group')
                                                    <i class="ri-group-line me-1"></i>مجموعة
                                                @else
                                                    <i class="ri-tent-line me-1"></i>معسكر
                                                @endif
                                                <br>
                                                <strong>{{ $reminder->target->title ?? $reminder->target->name ?? 'N/A' }}</strong>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $reminder->recipients_count }} طالب
                                            </span>
                                            @if($reminder->read_count > 0)
                                                <br>
                                                <small class="text-success">
                                                    ✓ {{ $reminder->read_count }} قراءة
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reminder->is_sent)
                                                <span class="badge bg-success">
                                                    <i class="ri-check-line"></i> مرسلة
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="ri-time-line"></i> قيد الانتظار
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @if($reminder->sent_at)
                                                    {{ $reminder->sent_at->format('Y/m/d H:i') }}
                                                    <br>
                                                    <small>({{ $reminder->sent_at->diffForHumans() }})</small>
                                                @elseif($reminder->remind_at)
                                                    موعد: {{ $reminder->remind_at->format('Y/m/d H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.reminders.show', $reminder) }}" class="btn btn-sm btn-icon btn-info" title="عرض">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                @if(!$reminder->is_sent)
                                                    <a href="{{ route('admin.reminders.edit', $reminder) }}" class="btn btn-sm btn-icon btn-warning" title="تعديل">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <form action="{{ route('admin.reminders.send', $reminder) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="إرسال الآن" onclick="return confirm('هل تريد إرسال هذا التذكير الآن؟')">
                                                            <i class="ri-send-plane-fill"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.reminders.destroy', $reminder) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-danger" title="حذف" onclick="return confirm('هل تريد حذف هذا التذكير؟')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="ri-notification-off-line fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">لا توجد تذكيرات بعد</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $reminders->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script>
function applyFilters() {
    const type = document.getElementById('typeFilter').value;
    const priority = document.getElementById('priorityFilter').value;
    const status = document.getElementById('statusFilter').value;

    let url = '{{ route('admin.reminders.index') }}?';
    const params = [];

    if (type) params.push(`type=${type}`);
    if (priority) params.push(`priority=${priority}`);
    if (status) params.push(`status=${status}`);

    window.location.href = url + params.join('&');
}
</script>
@endsection
