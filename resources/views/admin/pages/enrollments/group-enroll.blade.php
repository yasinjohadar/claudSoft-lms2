@extends('admin.layouts.master')

@section('page-title')
    تسجيل مجموعة - {{ $course->title }}
@stop

@section('css')
<style>
    .group-card-wrapper {
        margin-bottom: 1.5rem;
    }
    
    .group-card {
        border: 3px solid #e9ecef;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
        background: #ffffff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        display: flex;
        flex-direction: column;
        min-height: 320px;
    }

    .group-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .group-card:hover {
        border-color: #667eea;
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
        transform: translateY(-5px);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    }

    .group-card:hover::before {
        opacity: 1;
    }

    .group-card.selected {
        border-color: #667eea;
        border-width: 4px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        transform: translateY(-5px);
    }

    .group-card.selected::before {
        opacity: 1;
        height: 8px;
    }

    .group-card.selected::after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 20px;
        right: 20px;
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        animation: checkmark 0.4s ease-in-out;
        z-index: 10;
    }

    @keyframes checkmark {
        0% {
            transform: scale(0) rotate(-180deg);
            opacity: 0;
        }
        50% {
            transform: scale(1.2) rotate(0deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }

    .group-icon {
        width: 85px;
        height: 85px;
        border-radius: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        margin: 0 auto 1.5rem;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }

    .group-card:hover .group-icon {
        transform: scale(1.15) rotate(8deg);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
    }

    .group-card.selected .group-icon {
        transform: scale(1.15) rotate(8deg);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
    }

    .group-name {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 1rem;
        text-align: center;
        line-height: 1.5;
    }

    .group-description {
        font-size: 0.95rem;
        color: #495057;
        text-align: center;
        margin-bottom: 1.5rem;
        min-height: 50px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.7;
    }

    .members-stats {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        text-align: center;
        border: 2px solid rgba(102, 126, 234, 0.25);
    }

    .members-count {
        font-size: 3rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .members-label {
        font-size: 0.95rem;
        color: #495057;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .group-badge {
        border-radius: 12px;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        text-align: center;
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .group-creator {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 2px solid #e9ecef;
        text-align: center;
    }

    .group-creator small {
        font-size: 0.85rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
    }

    .group-creator i {
        color: #667eea;
        font-size: 1rem;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسجيل مجموعة كاملة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">تسجيل مجموعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card custom-card">

                <!-- Info Alert -->
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>تعليمات:</strong> اختر مجموعة واحدة لتسجيل جميع أعضائها في الكورس دفعة واحدة
                    </div>
                </div>

                @if($groups->isEmpty())
                    <!-- Empty State (Outside Form) -->
                    <div class="card-body">
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-users-slash fa-5x text-muted mb-4 opacity-25"></i>
                            <h4 class="text-muted mb-3">لا توجد مجموعات متاحة</h4>
                            <p class="text-muted">قم بإنشاء مجموعات أولاً لتتمكن من التسجيل الجماعي</p>
                            <a href="{{ route('courses.groups.create', $course->id) }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>إنشاء مجموعة جديدة
                            </a>
                        </div>
                    </div>
                @else
                    <form action="{{ route('courses.enrollments.group.process', $course->id) }}" method="POST">
                        @csrf

                        <!-- Groups Grid -->
                        <div class="card-body">
                            <div class="row" id="groupsContainer">
                        @foreach($groups as $group)
                            <div class="col-md-6 col-lg-4 group-card-wrapper">
                                <label class="w-100 m-0">
                                    <input type="radio"
                                           name="group_id"
                                           value="{{ $group->id }}"
                                           class="d-none group-radio"
                                           required>
                                    <div class="group-card">
                                        <div class="group-icon">
                                            <i class="fas fa-users"></i>
                                        </div>

                                        <h5 class="group-name">{{ $group->name }}</h5>

                                        @if($group->description)
                                            <p class="group-description">
                                                {{ Str::limit($group->description, 80) }}
                                            </p>
                                        @else
                                            <p class="group-description text-muted" style="font-style: italic;">
                                                لا يوجد وصف
                                            </p>
                                        @endif

                                        <div class="members-stats">
                                            <div class="members-count">{{ $group->members_count ?? 0 }}</div>
                                            <div class="members-label">عضو</div>
                                        </div>

                                        <div>
                                            @if($group->is_active)
                                                <span class="group-badge bg-success text-white">
                                                    <i class="fas fa-check-circle me-2"></i>نشطة
                                                </span>
                                            @else
                                                <span class="group-badge bg-secondary text-white">
                                                    <i class="fas fa-pause-circle me-2"></i>غير نشطة
                                                </span>
                                            @endif
                                        </div>

                                        @if($group->creator)
                                            <div class="group-creator">
                                                <small>
                                                    <i class="fas fa-user-circle"></i>
                                                    <span>{{ $group->creator->name }}</span>
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        @endforeach
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="card-header">
                            <h6 class="mb-0">خيارات التسجيل</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="send_notifications" id="sendNotifications" checked>
                                <label class="form-check-label" for="sendNotifications">
                                    إرسال إشعارات البريد الإلكتروني لجميع الأعضاء
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_enrolled" id="skipEnrolled" checked>
                                <label class="form-check-label" for="skipEnrolled">
                                    تخطي الأعضاء المسجلين مسبقاً
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-light">
                                    <i class="fas fa-arrow-right me-2"></i>رجوع
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-users me-2"></i>تسجيل المجموعة
                                </button>
                            </div>
                        </div>

                    </form>
                @endif

            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    const groupRadios = document.querySelectorAll('.group-radio');
    const submitBtn = document.getElementById('submitBtn');

    groupRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected class from all cards
            document.querySelectorAll('.group-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to chosen card
            if (this.checked) {
                this.closest('.group-card').classList.add('selected');
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    });
</script>
@stop
