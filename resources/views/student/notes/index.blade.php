@extends('student.layouts.master')

@section('page-title')
    المفكرة الشخصية
@stop

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    .note-card {
        transition: all 0.3s ease;
        border-left: 4px solid;
        cursor: pointer;
        user-select: none;
    }
    .note-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border-left-width: 6px;
    }
    .note-card:active {
        transform: translateY(-2px);
    }
    .note-card.pinned {
        background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
    }
    .category-badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 5px;
        border-radius: 4px;
    }
    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .view-toggle {
        display: flex;
        gap: 0.5rem;
    }
    .view-toggle .btn {
        border-radius: 8px;
    }
    
    /* View Note Modal Styles */
    #viewNoteModal .modal-content {
        border-radius: 15px;
        overflow: hidden;
        background: var(--default-body-bg, #ffffff);
        color: var(--default-text-color, #333);
    }
    
    [data-theme-mode="dark"] #viewNoteModal .modal-content {
        background: #1a1d29;
        color: #e9ecef;
    }
    
    #viewNoteModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        background: var(--default-body-bg, #ffffff);
        color: var(--default-text-color, #333);
    }
    
    [data-theme-mode="dark"] #viewNoteModal .modal-body {
        background: #1a1d29;
        color: #e9ecef;
    }
    
    #viewNoteContent {
        line-height: 1.8;
        font-size: 1.05rem;
        white-space: pre-wrap;
        word-wrap: break-word;
        background: var(--default-body-bg, #f8f9fa);
        color: var(--default-text-color, #333);
    }
    
    [data-theme-mode="dark"] #viewNoteContent {
        background: #252836;
        color: #e9ecef;
    }
    
    #viewNoteModal .modal-footer {
        background: var(--default-body-bg, #ffffff);
        border-top: 1px solid var(--default-border, #dee2e6);
    }
    
    [data-theme-mode="dark"] #viewNoteModal .modal-footer {
        background: #1a1d29;
        border-top-color: #2d3142;
    }
    
    /* Note Content Box */
    .note-content-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }
    
    [data-theme-mode="dark"] .note-content-box {
        background: linear-gradient(135deg, #252836 0%, #1a1d29 100%);
    }
    
    /* Reminder Box */
    .reminder-box {
        background: linear-gradient(135deg, #e7f3ff 0%, #cfe2ff 100%);
        border-right: 3px solid #0d6efd;
    }
    
    [data-theme-mode="dark"] .reminder-box {
        background: linear-gradient(135deg, #1e3a5f 0%, #153048 100%);
        border-right-color: #4a9eff;
    }
    
    /* Created Date Box */
    .created-date-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-right: 3px solid #6c757d;
    }
    
    [data-theme-mode="dark"] .created-date-box {
        background: linear-gradient(135deg, #2d3142 0%, #252836 100%);
        border-right-color: #6c757d;
    }
    
    .created-date-text {
        color: #333;
    }
    
    [data-theme-mode="dark"] .created-date-text {
        color: #e9ecef;
    }
    
    /* Badge colors in dark mode */
    [data-theme-mode="dark"] #viewNoteModal .badge.bg-warning {
        background-color: #f59e0b !important;
        color: #fff !important;
    }
    
    [data-theme-mode="dark"] #viewNoteModal .text-muted {
        color: #adb5bd !important;
    }
    
    /* Modal buttons in dark mode */
    [data-theme-mode="dark"] #viewNoteModal .btn-secondary {
        background-color: #2d3142;
        border-color: #2d3142;
        color: #e9ecef;
    }
    
    [data-theme-mode="dark"] #viewNoteModal .btn-primary {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    /* Text colors in dark mode */
    [data-theme-mode="dark"] #viewNoteModal .text-primary {
        color: #4a9eff !important;
    }
    
    [data-theme-mode="dark"] #viewNoteModal strong {
        color: #e9ecef;
    }
    
    /* Spin animation */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .spin {
        animation: spin 1s linear infinite;
    }
    
    /* Prevent click on card when clicking dropdown */
    .note-card .dropdown {
        position: relative;
        z-index: 10;
    }
    
    .note-card .dropdown-menu {
        z-index: 1050;
    }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">المفكرة الشخصية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">المفكرة الشخصية</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                    <i class="ri-add-line me-2"></i>إضافة ملاحظة جديدة
                </button>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- View Toggle & Filters -->
        <div class="card custom-card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="view-toggle">
                        <button class="btn btn-outline-primary active" id="gridViewBtn">
                            <i class="ri-layout-grid-line me-2"></i>عرض البطاقات
                        </button>
                        <button class="btn btn-outline-primary" id="calendarViewBtn">
                            <i class="ri-calendar-line me-2"></i>عرض التقويم
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-end">
                        <select class="form-select w-auto" id="categoryFilter">
                            <option value="">جميع التصنيفات</option>
                            @foreach(\App\Models\Note::getCategories() as $key => $category)
                                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                    {{ $category['icon'] }} {{ $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('student.notes.archived') }}" class="btn btn-outline-secondary">
                            <i class="ri-archive-line me-2"></i>الأرشيف
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid View -->
    <div id="gridView">
        <!-- Pinned Notes -->
        @if($pinnedNotes->count() > 0)
        <div class="mb-4">
            <h5 class="mb-3">📌 ملاحظات مثبتة</h5>
            <div class="notes-grid">
                @foreach($pinnedNotes as $note)
                    @include('student.notes.partials.note-card', ['note' => $note, 'pinned' => true])
                @endforeach
            </div>
        </div>
        @endif

        <!-- Regular Notes -->
        <div class="mb-4">
            <h5 class="mb-3">📄 جميع الملاحظات</h5>
            <div class="notes-grid">
                @forelse($notes as $note)
                    @include('student.notes.partials.note-card', ['note' => $note, 'pinned' => false])
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="ri-information-line fs-3"></i>
                            <p class="mb-0 mt-2">لا توجد ملاحظات بعد. ابدأ بإضافة ملاحظتك الأولى!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $notes->links() }}
        </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" style="display: none;">
            <div class="card custom-card">
                <div class="card-body">
                    <div id="notesCalendar"></div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Create Note Modal -->
<div class="modal fade" id="createNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة ملاحظة جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('student.notes.store') }}" method="POST" id="createNoteForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المحتوى</label>
                            <textarea name="content" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التصنيف</label>
                            <select name="category" class="form-select" required>
                                @foreach(\App\Models\Note::getCategories() as $key => $category)
                                    <option value="{{ $key }}">{{ $category['icon'] }} {{ $category['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اللون</label>
                            <input type="color" name="color" class="form-control" value="#3b82f6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تذكير في (اختياري)</label>
                            <input type="datetime-local" name="reminder_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_important" value="1" class="form-check-input" id="isImportant">
                                <label class="form-check-label" for="isImportant">ملاحظة مهمة ⭐</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ الملاحظة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="editNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editNoteForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="title" class="form-control" id="editTitle" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المحتوى</label>
                            <textarea name="content" class="form-control" rows="5" id="editContent" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التصنيف</label>
                            <select name="category" class="form-select" id="editCategory" required>
                                @foreach(\App\Models\Note::getCategories() as $key => $category)
                                    <option value="{{ $key }}">{{ $category['icon'] }} {{ $category['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اللون</label>
                            <input type="color" name="color" class="form-control" id="editColor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">تذكير في (اختياري)</label>
                            <input type="datetime-local" name="reminder_at" class="form-control" id="editReminder">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_important" value="1" class="form-check-input" id="editIsImportant">
                                <label class="form-check-label" for="editIsImportant">
                                    <i class="ri-star-line me-1"></i>ملاحظة مهمة
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Note Modal -->
<div class="modal fade" id="viewNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0" id="viewNoteHeader" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
                <div class="d-flex align-items-center w-100">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0" id="viewNoteTitle">
                            <i class="ri-file-text-line me-2"></i>عرض الملاحظة
                        </h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <!-- Category Badge -->
                <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge px-3 py-2 shadow-sm" id="viewNoteCategory" style="font-size: 0.95rem; font-weight: 600;">
                        <i class="ri-folder-line me-1"></i>
                    </span>
                    <span class="badge bg-warning text-dark ms-2 px-3 py-2 shadow-sm" id="viewNoteImportant" style="display: none; font-weight: 600;">
                        <i class="ri-star-fill me-1"></i>مهمة ⭐
                    </span>
                    <span class="badge bg-info text-white ms-2 px-3 py-2 shadow-sm" id="viewNotePinned" style="display: none; font-weight: 600;">
                        <i class="ri-pushpin-fill me-1"></i>مثبتة 📌
                    </span>
                </div>

                <!-- Content -->
                <div class="mb-4">
                    <div class="p-4 rounded shadow-sm note-content-box" style="border-right: 5px solid; min-height: 150px;" id="viewNoteContent">
                    </div>
                </div>

                <!-- Details -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6" id="viewNoteReminder" style="display: none;">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm reminder-box">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <i class="ri-alarm-line fs-5"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block mb-1" style="font-size: 0.85rem;">⏰ تذكير في</small>
                                <strong id="viewNoteReminderDate" class="text-primary"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 rounded shadow-sm created-date-box">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <i class="ri-time-line fs-5"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted d-block mb-1" style="font-size: 0.85rem;">📅 تاريخ الإنشاء</small>
                                <strong id="viewNoteCreatedAt" class="created-date-text"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-2"></i>إغلاق
                </button>
                <button type="button" class="btn btn-primary" id="editNoteFromViewBtn">
                    <i class="ri-edit-line me-2"></i>تعديل
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Toggle
    const gridViewBtn = document.getElementById('gridViewBtn');
    const calendarViewBtn = document.getElementById('calendarViewBtn');
    const gridView = document.getElementById('gridView');
    const calendarView = document.getElementById('calendarView');

    gridViewBtn.addEventListener('click', function() {
        gridView.style.display = 'block';
        calendarView.style.display = 'none';
        gridViewBtn.classList.add('active');
        calendarViewBtn.classList.remove('active');
    });

    calendarViewBtn.addEventListener('click', function() {
        gridView.style.display = 'none';
        calendarView.style.display = 'block';
        calendarViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');

        // Initialize calendar if not already
        if (!window.calendar) {
            initCalendar();
        }
    });

    // Category Filter
    document.getElementById('categoryFilter').addEventListener('change', function() {
        const category = this.value;
        window.location.href = category ? `{{ route('student.notes.index') }}?category=${category}` : `{{ route('student.notes.index') }}`;
    });

    // Initialize Calendar
    function initCalendar() {
        const calendarEl = document.getElementById('notesCalendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ar',
            direction: 'rtl',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                @foreach($notes->merge($pinnedNotes) as $note)
                    @if($note->reminder_at)
                    {
                        id: '{{ $note->id }}',
                        title: '{{ $note->title }}',
                        start: '{{ $note->reminder_at->format('Y-m-d H:i:s') }}',
                        backgroundColor: '{{ $note->color }}',
                        borderColor: '{{ $note->color }}',
                        extendedProps: {
                            content: '{{ str_replace(["\n", "\r"], ' ', addslashes($note->content)) }}',
                            category: '{{ $note->category }}',
                            color: '{{ $note->color }}',
                            is_important: {{ $note->is_important ? 'true' : 'false' }},
                            is_pinned: {{ $note->is_pinned ? 'true' : 'false' }},
                            reminder_at: '{{ $note->reminder_at ? $note->reminder_at->format('Y-m-d H:i:s') : '' }}'
                        }
                    },
                    @endif
                @endforeach
            ],
            eventClick: function(info) {
                // Get note data from event
                const note = {
                    id: info.event.id,
                    title: info.event.title,
                    content: info.event.extendedProps.content || '',
                    category: info.event.extendedProps.category || 'personal',
                    color: info.event.extendedProps.color || '#3b82f6',
                    is_important: info.event.extendedProps.is_important || false,
                    is_pinned: info.event.extendedProps.is_pinned || false,
                    reminder_at: info.event.extendedProps.reminder_at || null,
                    created_at: info.event.start
                };
                viewNote(note);
            }
        });
        calendar.render();
        window.calendar = calendar;
    }
});

// Toggle Pin
function togglePin(noteId) {
    fetch(`/student/notes/${noteId}/pin`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Toggle Favorite
function toggleFavorite(noteId) {
    fetch(`/student/notes/${noteId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Archive Note
function archiveNote(noteId) {
    if (confirm('هل أنت متأكد من أرشفة هذه الملاحظة؟')) {
        fetch(`/student/notes/${noteId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Edit Note
function editNote(note) {
    if (!note || !note.id) {
        console.error('Invalid note data for editing:', note);
        return;
    }
    
    // Use route helper to generate the correct URL
    const updateUrl = '{{ route("student.notes.update", ":id") }}'.replace(':id', note.id);
    document.getElementById('editNoteForm').action = updateUrl;
    
    document.getElementById('editTitle').value = note.title || '';
    document.getElementById('editContent').value = note.content || '';
    document.getElementById('editCategory').value = note.category || 'personal';
    document.getElementById('editColor').value = note.color || '#3b82f6';
    
    // Handle reminder_at - it might be a string or Date object
    let reminderValue = '';
    if (note.reminder_at) {
        try {
            const reminderDate = new Date(note.reminder_at);
            if (!isNaN(reminderDate.getTime())) {
                // Format as YYYY-MM-DDTHH:mm for datetime-local input
                const year = reminderDate.getFullYear();
                const month = String(reminderDate.getMonth() + 1).padStart(2, '0');
                const day = String(reminderDate.getDate()).padStart(2, '0');
                const hours = String(reminderDate.getHours()).padStart(2, '0');
                const minutes = String(reminderDate.getMinutes()).padStart(2, '0');
                reminderValue = `${year}-${month}-${day}T${hours}:${minutes}`;
            }
        } catch (e) {
            console.error('Error parsing reminder_at:', e);
        }
    }
    document.getElementById('editReminder').value = reminderValue;
    
    // Handle is_important checkbox
    const isImportantCheckbox = document.getElementById('editIsImportant');
    if (isImportantCheckbox) {
        isImportantCheckbox.checked = note.is_important === true || note.is_important === 'true' || note.is_important === 1 || note.is_important === '1';
    }

    const modal = new bootstrap.Modal(document.getElementById('editNoteModal'));
    modal.show();
}

// View Note
function viewNote(note) {
    // Ensure note is an object with required properties
    if (!note || typeof note !== 'object') {
        console.error('Invalid note data:', note);
        return;
    }
    
    const categories = @json(\App\Models\Note::getCategories());
    const categoryInfo = categories[note.category] || categories['personal'];
    const noteColor = note.color || categoryInfo.color || '#3b82f6';
    
    // Convert hex to rgb for gradient
    const hexToRgb = (hex) => {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    };
    
    const rgb = hexToRgb(noteColor);
    const lighterColor = rgb ? `rgb(${Math.min(255, rgb.r + 30)}, ${Math.min(255, rgb.g + 30)}, ${Math.min(255, rgb.b + 30)})` : noteColor;
    
    // Set header gradient
    const header = document.getElementById('viewNoteHeader');
    header.style.background = `linear-gradient(135deg, ${noteColor} 0%, ${lighterColor} 100%)`;
    
    // Set title
    document.getElementById('viewNoteTitle').innerHTML = `<i class="ri-file-text-line me-2"></i>${note.title}`;
    
    // Set category badge
    const categoryBadge = document.getElementById('viewNoteCategory');
    categoryBadge.textContent = `${categoryInfo.icon} ${categoryInfo.name}`;
    categoryBadge.style.backgroundColor = categoryInfo.color + '20';
    categoryBadge.style.color = categoryInfo.color;
    
    // Set content
    const contentBox = document.getElementById('viewNoteContent');
    contentBox.innerHTML = note.content.replace(/\n/g, '<br>');
    contentBox.style.borderRightColor = noteColor;
    
    // Adjust colors for dark mode
    const isDarkMode = document.documentElement.getAttribute('data-theme-mode') === 'dark';
    if (isDarkMode) {
        // In dark mode, use lighter border color
        const rgb = hexToRgb(noteColor);
        if (rgb) {
            const lighterBorder = `rgb(${Math.min(255, rgb.r + 50)}, ${Math.min(255, rgb.g + 50)}, ${Math.min(255, rgb.b + 50)})`;
            contentBox.style.borderRightColor = lighterBorder;
        }
    }
    
    // Set important badge
    if (note.is_important) {
        document.getElementById('viewNoteImportant').style.display = 'inline-block';
    } else {
        document.getElementById('viewNoteImportant').style.display = 'none';
    }
    
    // Set pinned badge
    if (note.is_pinned) {
        document.getElementById('viewNotePinned').style.display = 'inline-block';
    } else {
        document.getElementById('viewNotePinned').style.display = 'none';
    }
    
    // Set reminder
    if (note.reminder_at && note.reminder_at !== 'null' && note.reminder_at !== null) {
        try {
            const reminderDate = new Date(note.reminder_at);
            if (!isNaN(reminderDate.getTime()) && reminderDate.getTime() > 0) {
                document.getElementById('viewNoteReminderDate').textContent = reminderDate.toLocaleString('ar-SA', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById('viewNoteReminder').style.display = 'block';
            } else {
                document.getElementById('viewNoteReminder').style.display = 'none';
            }
        } catch (e) {
            console.error('Error parsing reminder_at:', e);
            document.getElementById('viewNoteReminder').style.display = 'none';
        }
    } else {
        document.getElementById('viewNoteReminder').style.display = 'none';
    }
    
    // Set created at
    if (note.created_at) {
        try {
            const createdAt = new Date(note.created_at);
            if (!isNaN(createdAt.getTime()) && createdAt.getTime() > 0) {
                document.getElementById('viewNoteCreatedAt').textContent = createdAt.toLocaleString('ar-SA', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                document.getElementById('viewNoteCreatedAt').textContent = 'غير محدد';
            }
        } catch (e) {
            console.error('Error parsing created_at:', e);
            document.getElementById('viewNoteCreatedAt').textContent = 'غير محدد';
        }
    } else {
        document.getElementById('viewNoteCreatedAt').textContent = 'غير محدد';
    }
    
    // Set edit button
    document.getElementById('editNoteFromViewBtn').onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('viewNoteModal')).hide();
        editNote(note);
    };
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewNoteModal'));
    modal.show();
}

// Delete Note
function deleteNote(noteId, event) {
    // Prevent event bubbling if called from dropdown
    if (event) {
        event.stopPropagation();
    }
    
    if (confirm('هل أنت متأكد من حذف هذه الملاحظة؟ سيتم حذفها نهائياً.')) {
        // Show loading
        const loadingToast = document.createElement('div');
        loadingToast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
        loadingToast.style.zIndex = '9999';
        loadingToast.innerHTML = '<div class="alert alert-info"><i class="ri-loader-4-line spin me-2"></i>جاري الحذف...</div>';
        document.body.appendChild(loadingToast);
        
        const deleteUrl = `/student/notes/${noteId}`;
        console.log('Deleting note:', noteId, 'URL:', deleteUrl);
        
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (!response.ok) {
                return response.json().then(data => {
                    console.error('Delete error:', data);
                    throw new Error(data.message || 'حدث خطأ أثناء الحذف');
                });
            }
            return response.json();
        })
        .then(data => {
            // Remove loading
            loadingToast.remove();
            
            if (data.success) {
                // Show success toast
                const successToast = document.createElement('div');
                successToast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
                successToast.style.zIndex = '9999';
                successToast.innerHTML = `<div class="alert alert-success alert-dismissible fade show">
                    <i class="ri-checkbox-circle-line me-2"></i>${data.message || 'تم حذف الملاحظة بنجاح'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
                document.body.appendChild(successToast);
                
                // Reload after 1 second
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert(data.message || 'فشل حذف الملاحظة');
            }
        })
        .catch(error => {
            // Remove loading
            loadingToast.remove();
            
            console.error('Error:', error);
            
            // Show error toast
            const errorToast = document.createElement('div');
            errorToast.className = 'position-fixed top-0 start-50 translate-middle-x mt-3';
            errorToast.style.zIndex = '9999';
            errorToast.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">
                <i class="ri-error-warning-line me-2"></i>${error.message || 'حدث خطأ أثناء حذف الملاحظة'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
            document.body.appendChild(errorToast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                errorToast.remove();
            }, 5000);
        });
    }
}
</script>
@endsection

