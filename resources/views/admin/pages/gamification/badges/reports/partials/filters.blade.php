@php
    $showRarity = $showRarity ?? true;
    $searchLabel = $searchLabel ?? 'بحث';
    $searchPlaceholder = $searchPlaceholder ?? 'ابحث...';
@endphp

<form id="badgeReportFiltersForm" method="GET" class="group-show-filters mb-0">
    <div class="row g-3 align-items-end">
        <div class="col-xl-3 col-lg-4 col-md-6">
            <label class="form-label" for="badgeReportSearch">{{ $searchLabel }}</label>
            <input id="badgeReportSearch" type="text" name="q" class="form-control"
                placeholder="{{ $searchPlaceholder }}" value="{{ request('q') }}">
        </div>
        <div class="col-xl-2 col-lg-3 col-md-6">
            <label class="form-label" for="badgeReportCourse">الكورس</label>
            <select name="course_id" id="badgeReportCourse" class="form-select">
                <option value="">كل الكورسات</option>
                @foreach ($courses ?? [] as $course)
                    <option value="{{ $course->id }}" {{ (int) request('course_id') === (int) $course->id ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-6">
            <label class="form-label" for="badgeReportGroup">المجموعة</label>
            <select name="group_id" id="badgeReportGroup" class="form-select">
                @include('admin.pages.gamification.badges.reports.partials.group-options', [
                    'allGroups' => $allGroups ?? collect(),
                ])
            </select>
        </div>
        @if ($showRarity)
            <div class="col-xl-2 col-lg-3 col-md-6">
                <label class="form-label" for="badgeReportRarity">الندرة</label>
                <select name="rarity" id="badgeReportRarity" class="form-select">
                    <option value="">كل الندرات</option>
                    <option value="common" {{ request('rarity') === 'common' ? 'selected' : '' }}>عادي</option>
                    <option value="rare" {{ request('rarity') === 'rare' ? 'selected' : '' }}>نادر</option>
                    <option value="epic" {{ request('rarity') === 'epic' ? 'selected' : '' }}>ملحمي</option>
                    <option value="legendary" {{ request('rarity') === 'legendary' ? 'selected' : '' }}>أسطوري</option>
                    <option value="mythic" {{ request('rarity') === 'mythic' ? 'selected' : '' }}>خرافي</option>
                </select>
            </div>
        @endif
        <div class="col-xl-12">
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fe fe-search me-1"></i>بحث
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="badgeReportFilterReset">
                    <i class="fe fe-rotate-cw me-1"></i>مسح
                </button>
            </div>
        </div>
    </div>
</form>
