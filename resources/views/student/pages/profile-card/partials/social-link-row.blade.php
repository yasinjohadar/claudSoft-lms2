@php
    $link = $link ?? [];
    $platform = $link['platform'] ?? 'custom';
    $preset = $socialPlatforms[$platform] ?? ($socialPlatforms['custom'] ?? []);
@endphp
<div class="profile-card-social-row social-link-row" data-index="{{ $index }}">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label fs-12">المنصة</label>
            <select name="social_links[{{ $index }}][platform]" class="form-select form-select-sm js-platform-select">
                @foreach($socialPlatforms as $key => $meta)
                    <option value="{{ $key }}" @selected($platform === $key)>{{ $meta['default_label'] ?? $key }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label fs-12">الرابط</label>
            <input type="url" name="social_links[{{ $index }}][url]" class="form-control form-control-sm" value="{{ $link['url'] ?? '' }}" placeholder="{{ $preset['url_hint'] ?? 'https://' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fs-12">التسمية</label>
            <input type="text" name="social_links[{{ $index }}][label]" class="form-control form-control-sm js-link-label" value="{{ $link['label'] ?? ($preset['default_label'] ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fs-12">الأيقونة (Font Awesome)</label>
            <input type="text" name="social_links[{{ $index }}][icon]" class="form-control form-control-sm js-link-icon" dir="ltr" value="{{ $link['icon'] ?? ($preset['default_icon'] ?? 'fas fa-link') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fs-12">ترتيب</label>
            <input type="number" name="social_links[{{ $index }}][sort_order]" class="form-control form-control-sm" min="0" value="{{ $link['sort_order'] ?? $index }}">
        </div>
        <div class="col-md-2">
            <div class="form-check mb-2">
                <input type="hidden" name="social_links[{{ $index }}][enabled]" value="0">
                <input class="form-check-input" type="checkbox" name="social_links[{{ $index }}][enabled]" value="1" @checked(($link['enabled'] ?? true))>
                <label class="form-check-label fs-12">مفعّل</label>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-100 js-remove-social">
                <i class="fe fe-trash-2 me-1"></i>حذف
            </button>
        </div>
    </div>
</div>
