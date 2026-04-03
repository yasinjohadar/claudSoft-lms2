@extends('student.layouts.master')

@section('page-title')
    ملاحظات: {{ $course->title }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ملاحظات الكورس</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.course-notes.index') }}">ملاحظات الكورسات</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($course->title, 40) }}</li>
                    </ol>
                </nav>
                <p class="text-muted fs-14 mb-0 mt-2">
                    <i class="ri-book-line me-1"></i>{{ $course->title }}
                </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="{{ route('student.course-notes.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-right-line me-2"></i>كل الملاحظات
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseNoteModal">
                    <i class="ri-add-line me-2"></i>إضافة ملاحظة
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            @forelse($notes as $note)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card custom-card h-100 {{ $note->is_important ? 'border-warning' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">{{ $note->title }}</h5>
                                    @if($note->is_important)
                                        <span class="badge bg-warning">⭐ مهم</span>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                               onclick='editCourseNote(@json($note))'>
                                                <i class="ri-edit-line me-2"></i>تعديل
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                                               onclick="deleteCourseNote({{ $note->id }})">
                                                <i class="ri-delete-bin-line me-2"></i>حذف
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-2">
                                @if($note->lesson)
                                    <small class="text-muted d-block">
                                        <i class="ri-file-list-line me-1"></i>
                                        <strong>الدرس:</strong> {{ $note->lesson->title }}
                                    </small>
                                @endif
                                @if($note->video_timestamp)
                                    <small class="text-muted d-block">
                                        <i class="ri-time-line me-1"></i>
                                        <strong>التوقيت:</strong> {{ $note->video_timestamp }}
                                    </small>
                                @endif
                            </div>

                            <p class="card-text">{{ Str::limit($note->content, 200) }}</p>

                            <div class="border-top pt-2 mt-2">
                                <small class="text-muted">
                                    <i class="ri-calendar-line"></i> {{ $note->created_at->format('Y/m/d') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-information-line fs-3"></i>
                        <p class="mb-0 mt-2">لا توجد ملاحظات لهذا الكورس بعد</p>
                        <button type="button" class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#createCourseNoteModal">
                            <i class="ri-add-line me-1"></i>أضف أول ملاحظة
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>

<div class="modal fade" id="createCourseNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة ملاحظة — {{ Str::limit($course->title, 35) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('student.course-notes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">الدرس (اختياري)</label>
                            <select name="lesson_id" class="form-select" id="lessonSelectByCourse">
                                <option value="">اختر الدرس</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المحتوى</label>
                            <textarea name="content" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">توقيت الفيديو (اختياري)</label>
                            <input type="text" name="video_timestamp" class="form-control" placeholder="مثال: 15:30">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_important" class="form-check-input" id="isImportantCreateByCourse">
                                <label class="form-check-label" for="isImportantCreateByCourse">ملاحظة مهمة ⭐</label>
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

<div class="modal fade" id="editCourseNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الملاحظة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCourseNoteForm" method="POST">
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
                            <label class="form-label">توقيت الفيديو (اختياري)</label>
                            <input type="text" name="video_timestamp" class="form-control" id="editTimestamp">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_important" class="form-check-input" id="editIsImportant">
                                <label class="form-check-label" for="editIsImportant">ملاحظة مهمة ⭐</label>
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
@endsection

@section('scripts')
<script>
const courseIdForLessons = {{ (int) $course->id }};

function loadLessonsForCourse() {
    const lessonSelect = document.getElementById('lessonSelectByCourse');
    if (!lessonSelect) return;
    fetch(`/api/courses/${courseIdForLessons}/lessons`)
        .then(response => response.json())
        .then(lessons => {
            lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
            lessons.forEach(lesson => {
                lessonSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
            });
        })
        .catch(() => {});
}

document.getElementById('createCourseNoteModal')?.addEventListener('show.bs.modal', loadLessonsForCourse);

function editCourseNote(note) {
    document.getElementById('editCourseNoteForm').action = `/student/course-notes/${note.id}`;
    document.getElementById('editTitle').value = note.title;
    document.getElementById('editContent').value = note.content;
    document.getElementById('editTimestamp').value = note.video_timestamp || '';
    document.getElementById('editIsImportant').checked = note.is_important;

    const modal = new bootstrap.Modal(document.getElementById('editCourseNoteModal'));
    modal.show();
}

function deleteCourseNote(noteId) {
    if (confirm('هل أنت متأكد من حذف هذه الملاحظة؟')) {
        fetch(`/student/course-notes/${noteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
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
