@extends('frontend.layouts.master')

@section('title', 'الطلاب')
@section('meta_description', 'تعرف على طلابنا المميزين في المنصة التعليمية')

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">الطلاب المميزون</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">الطلاب</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Students Section -->
<section class="students-section py-5">
    <div class="container">

        <div class="students-grid">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                @forelse($students as $student)
                <div class="col">
                    <div class="student-card">
                        <a href="{{ route('frontend.students.show', $student->id) }}" class="card-link">
                            <div class="student-avatar">
                                @if($student->avatar)
                                    <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $student->name }}">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="student-info">
                                <h5 class="student-name">{{ $student->name }}</h5>
                                <div class="student-meta">
                                    <span class="badge bg-primary">
                                        <i class="fa-solid fa-calendar"></i>
                                        انضم {{ $student->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span class="view-profile">عرض الملف الشخصي <i class="fa-solid fa-arrow-left"></i></span>
                            </div>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="empty-state text-center py-5">
                        <i class="fa-solid fa-users fa-3x text-muted mb-3"></i>
                        <h4>لا يوجد طلاب متاحون حالياً</h4>
                        <p class="text-muted">سيتم عرض الطلاب المسجلين هنا قريباً</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
        <div class="pagination-wrapper mt-5">
            <nav aria-label="Page navigation">
                {{ $students->links('pagination::bootstrap-5') }}
            </nav>
        </div>
        @endif

    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.page-header .breadcrumb {
    background: transparent;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.page-header .breadcrumb-item.active {
    color: #ffffff;
}

.page-header .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
}

/* Students Section */
.students-section {
    background-color: #f8f9fa;
    min-height: 70vh;
}

.students-stats {
    background: white;
    padding: 30px;
    border-radius: 10px;
    border-right: 4px solid var(--main-Color);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.text-main {
    color: var(--main-Color);
    font-weight: 700;
    font-size: 1.3rem;
}

/* Student Card */
.student-card {
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.student-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.card-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.student-avatar {
    padding: 30px 20px 20px;
    text-align: center;
    background: #f8f9fa;
}

.student-avatar img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--secondary-Color);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: bold;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.student-info {
    padding: 20px;
    text-align: center;
    flex-grow: 1;
}

.student-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--secondary-Color);
    margin-bottom: 10px;
}


.student-meta {
    margin-top: 15px;
}

.student-meta .badge {
    padding: 8px 15px;
    font-size: 0.85rem;
    font-weight: 500;
}

.card-footer {
    background: var(--main-Color);
    padding: 15px;
    text-align: center;
    border-top: none;
}

.view-profile {
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.view-profile i {
    transition: transform 0.3s ease;
}

.student-card:hover .view-profile i {
    transform: translateX(-5px);
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    gap: 5px;
}

.pagination-wrapper .page-link {
    color: var(--secondary-Color);
    border: 1px solid #dee2e6;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-wrapper .page-link:hover {
    background: var(--main-Color);
    color: #ffffff;
    border-color: var(--main-Color);
}

.pagination-wrapper .page-item.active .page-link {
    background: var(--secondary-Color);
    border-color: var(--secondary-Color);
    color: #ffffff;
}

.pagination-wrapper .page-item.disabled .page-link {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .student-avatar img,
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }

    .student-name {
        font-size: 1.1rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }

    .students-stats {
        padding: 20px;
    }

    .text-main {
        font-size: 1.1rem;
    }
}
</style>

@endsection
