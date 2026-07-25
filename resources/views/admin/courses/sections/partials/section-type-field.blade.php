@php
    use App\Models\CourseSection;

    $sectionTypeOptions = CourseSection::visualPresets();
    $selectedSectionType = old('section_type', isset($section) && $section ? ($section->section_type ?: 'default') : 'video');
@endphp
<div class="admin-section-type-picker">
    <div class="row g-3">
        @foreach($sectionTypeOptions as $typeKey => $typeMeta)
            <div class="col-6 col-md-4">
                <label class="admin-section-type-option admin-section-type-option--{{ $typeMeta['tone'] }} {{ $selectedSectionType === $typeKey ? 'is-selected' : '' }}">
                    <input type="radio"
                           name="section_type"
                           value="{{ $typeKey }}"
                           class="admin-section-type-option__input"
                           {{ $selectedSectionType === $typeKey ? 'checked' : '' }}
                           required>
                    <span class="admin-section-type-option__icon">
                        <i class="fe {{ $typeMeta['icon'] }}"></i>
                    </span>
                    <span class="admin-section-type-option__label">{{ $typeMeta['label'] }}</span>
                </label>
            </div>
        @endforeach
    </div>
    @error('section_type')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
