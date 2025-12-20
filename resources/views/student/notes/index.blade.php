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
    }
    .note-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
                                <input type="checkbox" name="is_important" class="form-check-input" id="isImportant">
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
                            content: '{{ str_replace(["\n", "\r"], ' ', addslashes($note->content)) }}'
                        }
                    },
                    @endif
                @endforeach
            ],
            eventClick: function(info) {
                alert('الملاحظة: ' + info.event.title + '\n\n' + info.event.extendedProps.content);
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
    document.getElementById('editNoteForm').action = `/student/notes/${note.id}`;
    document.getElementById('editTitle').value = note.title;
    document.getElementById('editContent').value = note.content;
    document.getElementById('editCategory').value = note.category;
    document.getElementById('editColor').value = note.color;
    document.getElementById('editReminder').value = note.reminder_at ? note.reminder_at.replace(' ', 'T').substring(0, 16) : '';

    const modal = new bootstrap.Modal(document.getElementById('editNoteModal'));
    modal.show();
}

// Delete Note
function deleteNote(noteId) {
    if (confirm('هل أنت متأكد من حذف هذه الملاحظة؟')) {
        fetch(`/student/notes/${noteId}`, {
            method: 'DELETE',
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
</script>
@endsection
