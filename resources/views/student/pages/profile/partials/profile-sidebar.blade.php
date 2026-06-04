@php
    $photoPath = $student->photo ?? null;
    $photoUrl = $photoPath ? student_profile_photo_url($student, $photoPath) : '';
@endphp

<div class="card custom-card student-quizzes-panel mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-image text-primary"></i>
            </span>
            <h6 class="card-title mb-0">الصورة الشخصية</h6>
        </div>

        <div class="text-center mb-4">
            <div class="student-profile-photo mx-auto">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $student->name }}" class="student-profile-photo__img"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="student-profile-photo__placeholder d-none">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                @else
                    <div class="student-profile-photo__placeholder">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('student.profile.edit') }}#photo-section" class="btn btn-primary rounded-pill">
                <i class="fe fe-edit me-1"></i>تعديل الصورة
            </a>
            @if($photoPath)
                <form action="{{ route('student.profile.delete-photo') }}" method="POST" class="delete-photo-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill w-100">
                        <i class="fe fe-trash-2 me-1"></i>حذف الصورة
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="card custom-card student-quizzes-panel">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="avatar avatar-sm bg-warning-transparent">
                <i class="fe fe-shield text-warning"></i>
            </span>
            <h6 class="card-title mb-0">الأمان</h6>
        </div>

        <div class="student-profile-field mb-3">
            <span class="student-profile-field__icon">
                <i class="fe fe-lock"></i>
            </span>
            <div>
                <span class="student-profile-field__label">كلمة المرور</span>
                <span class="student-profile-field__value">••••••••</span>
            </div>
        </div>

        <a href="{{ route('student.profile.edit') }}#password-section" class="btn btn-warning rounded-pill w-100">
            <i class="fe fe-key me-1"></i>تغيير كلمة المرور
        </a>
    </div>
</div>
