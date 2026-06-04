@php
    $photoPath = $student->photo ?? null;
    $photoUrl = $photoPath ? student_profile_photo_url($student, $photoPath) : '';
    $displayPhone = $student->full_phone ?? trim(($student->country_code ?? '') . ($student->phone ?? '')) ?: $student->phone;
@endphp

<div class="student-profile-hero mb-4">
    <div class="student-profile-hero__glow"></div>
    <div class="row align-items-center g-4 position-relative">
        <div class="col-auto">
            <div class="student-profile-hero__avatar-wrap">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $student->name }}" class="student-profile-hero__avatar"
                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="student-profile-hero__avatar-placeholder d-none">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                @else
                    <div class="student-profile-hero__avatar-placeholder">
                        {{ mb_substr($student->name, 0, 1) }}
                    </div>
                @endif
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
