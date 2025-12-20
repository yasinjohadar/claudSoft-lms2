@extends('student.layouts.master')

@section('page-title')
    الملاحظات المؤرشفة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">الملاحظات المؤرشفة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.notes.index') }}">المفكرة الشخصية</a></li>
                        <li class="breadcrumb-item active">الأرشيف</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.notes.index') }}" class="btn btn-primary">
                    <i class="ri-arrow-right-line me-2"></i>العودة للمفكرة
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

        <!-- Archived Notes Grid -->
        <div class="notes-grid">
            @forelse($archivedNotes as $note)
                <div class="card custom-card" style="border-left: 4px solid {{ $note->color }};">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-1">{{ $note->title }}</h5>
                        <button class="btn btn-sm btn-success" onclick="unarchiveNote({{ $note->id }})">
                            <i class="ri-inbox-unarchive-line"></i> استعادة
                        </button>
                    </div>
                    <p class="card-text text-muted">{{ Str::limit($note->content, 150) }}</p>
                    <small class="text-muted">أرشفت: {{ $note->updated_at->diffForHumans() }}</small>
                </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="ri-inbox-line fs-3"></i>
                        <p class="mb-0 mt-2">لا توجد ملاحظات مؤرشفة</p>
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</div>

<style>
    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
</style>

<script>
function unarchiveNote(noteId) {
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
</script>
@endsection
