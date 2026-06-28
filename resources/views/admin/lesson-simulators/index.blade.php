@extends('admin.layouts.master')

@section('page-title')
    محاكيات الدروس
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">محاكيات الدروس التفاعلية</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.lesson-simulators.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-folder-tree me-1"></i> التصنيفات
                </a>
                <a href="{{ route('admin.lesson-simulators.ai.create') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-bolt me-1"></i> توليد بالذكاء الاصطناعي
                </a>
                <a href="{{ route('admin.lesson-simulators.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إنشاء محاكاة
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="simulator_category_id" class="form-select">
                            <option value="">كل التصنيفات</option>
                            @foreach($categoryOptions as $id => $label)
                                <option value="{{ $id }}" @selected((string) request('simulator_category_id') === (string) $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="topic_key" class="form-select">
                            <option value="">كل المواضيع</option>
                            @foreach($topics as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach($items as $key => $label)
                                        <option value="{{ $key }}" @selected(request('topic_key') === $key)>{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="course_id" class="form-select">
                            <option value="">كل الكورسات</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">تصفية</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>العنوان</th>
                            <th>التصنيف</th>
                            <th>الموضوع</th>
                            <th>الحالة</th>
                            <th>المنشئ</th>
                            <th>تاريخ الإنشاء</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($simulators as $sim)
                            <tr>
                                <td>{{ $sim->title }}</td>
                                <td class="small">{{ $sim->category?->full_path ?? '—' }}</td>
                                <td>{{ \App\Services\Simulator\SimulatorTopicRegistry::label($sim->topic_key) }}</td>
                                <td><span class="badge bg-{{ $sim->status === 'published' ? 'success' : ($sim->status === 'draft' ? 'warning' : 'secondary') }}">{{ $statuses[$sim->status] ?? $sim->status }}</span></td>
                                <td>{{ $sim->creator?->name ?? '—' }}</td>
                                <td>{{ $sim->created_at?->format('Y-m-d') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.lesson-simulators.preview', $sim) }}" class="btn btn-sm btn-outline-info" target="_blank">معاينة</a>
                                    <a href="{{ route('admin.lesson-simulators.edit', $sim) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form action="{{ route('admin.lesson-simulators.destroy', $sim) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف «{{ addslashes($sim->title) }}» نهائياً؟ لا يمكن التراجع.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">لا توجد محاكيات بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($simulators->hasPages())
                <div class="card-footer">{{ $simulators->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
