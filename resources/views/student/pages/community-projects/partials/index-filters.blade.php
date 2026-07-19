<form method="GET" action="{{ route('student.community-projects.index') }}" class="group-show-filters mb-0">
    <div class="row g-3 align-items-end">
        <div class="col-xl-5 col-lg-5 col-md-6">
            <label class="form-label" for="communitySearch">بحث</label>
            <input type="text" name="q" id="communitySearch" class="form-control"
                   placeholder="ابحث بعنوان المشروع أو الملخص..." value="{{ request('q') }}">
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6">
            <label class="form-label" for="communityChallenge">التحدي</label>
            <select name="challenge_id" id="communityChallenge" class="form-select">
                <option value="">كل التحديات</option>
                @foreach($challenges as $challenge)
                    <option value="{{ $challenge->id }}" @selected((string) request('challenge_id') === (string) $challenge->id)>
                        {{ $challenge->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-12">
            <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="fe fe-search me-1"></i>بحث
            </button>
        </div>
        @if(request()->hasAny(['q', 'challenge_id']))
            <div class="col-12">
                <a href="{{ route('student.community-projects.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                </a>
            </div>
        @endif
    </div>
</form>
