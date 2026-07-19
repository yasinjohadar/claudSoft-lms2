<form method="GET" action="{{ route('student.project-challenges.index') }}" class="group-show-filters mb-0">
    <div class="row g-3 align-items-end">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <label class="form-label" for="challengeSearch">بحث</label>
            <input type="text" name="q" id="challengeSearch" class="form-control"
                   placeholder="ابحث بعنوان التحدي..." value="{{ request('q') }}">
        </div>
        <div class="col-xl-2 col-lg-3 col-md-6">
            <label class="form-label" for="challengeDifficulty">المستوى</label>
            <select name="difficulty" id="challengeDifficulty" class="form-select">
                <option value="">كل المستويات</option>
                @foreach(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('difficulty') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6">
            <label class="form-label" for="challengeType">النوع</label>
            <select name="type" id="challengeType" class="form-select">
                <option value="">كل الأنواع</option>
                @foreach(['team_project' => 'مشروع فريق', 'open_challenge' => 'تحدي مفتوح', 'hackathon' => 'هاكاثون', 'capstone' => 'مشروع تخرج'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-6">
            <label class="form-label d-block">&nbsp;</label>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured"
                       @checked(request()->boolean('featured'))>
                <label class="form-check-label" for="featured">المميزة فقط</label>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-6">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fe fe-search me-1"></i>تصفية
            </button>
        </div>
        @if(request()->hasAny(['q', 'difficulty', 'type', 'featured']))
            <div class="col-12">
                <a href="{{ route('student.project-challenges.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                </a>
            </div>
        @endif
    </div>
</form>
