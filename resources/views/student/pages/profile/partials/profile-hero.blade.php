@php
    $photoPath = $student->photo ?? null;
    $photoUrl = student_profile_photo_url($student);
    $usesLogoAvatar = empty($photoPath);
    $displayPhone = $student->full_phone ?? trim(($student->country_code ?? '') . ($student->phone ?? '')) ?: $student->phone;
@endphp

<div class="student-profile-hero mb-4">
    <div class="student-profile-hero__glow"></div>
    <div class="row align-items-center g-4 position-relative">
        <div class="col-auto">
            <div class="student-profile-hero__avatar-wrap">
                <img src="{{ $photoUrl }}"
                     alt="{{ $student->name }}"
                     class="student-profile-hero__avatar {{ $usesLogoAvatar ? 'student-avatar--logo' : '' }}"
                     onerror="this.onerror=null;this.src='{{ student_default_avatar_url() }}';this.classList.add('student-avatar--logo');">
            </div>
        </div>
        <div class="col min-w-0">
            <h2 class="student-profile-hero__name mb-2">{{ $student->name_ar ?? $student->name }}</h2>
            <div class="student-profile-hero__meta">
                <span><i class="fe fe-mail me-1"></i>{{ $student->email }}</span>
                @if($displayPhone)
                    <span><i class="fe fe-phone me-1"></i>{{ $displayPhone }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
