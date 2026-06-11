@extends('admin.layouts.master')

@section('page-title')
    منح / تعويض نقاط
@stop

@section('styles')
    @include('admin.pages.student-gifts.partials.select2-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">منح أو تعويض نقاط — استهداف مرن</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.points.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>
                        <div class="card-body">
                            @php
                                $targetType = old('target_type', 'single');
                                $operation = old('operation', 'bonus');
                            @endphp

                            <form method="POST" action="{{ route('admin.gamification.points.store') }}" id="points-grant-form">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label d-block">نوع العملية <span class="text-danger">*</span></label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input operation-radio" type="radio" name="operation" id="op_bonus" value="bonus" {{ $operation === 'bonus' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="op_bonus">منح نقاط (مكافأة)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input operation-radio" type="radio" name="operation" id="op_deduct" value="deduct" {{ $operation === 'deduct' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="op_deduct">خصم نقاط</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input operation-radio" type="radio" name="operation" id="op_backfill" value="backfill" {{ $operation === 'backfill' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="op_backfill">تعويض من النشاط السابق</label>
                                    </div>
                                    <p class="text-muted fs-12 mt-2 mb-0">التعويض يفحص الدروس والفيديوهات والاختبارات والواجبات وإكمال الكورسات ويمنح الناقص فقط.</p>
                                </div>

                                <div class="mb-4" id="points-input-wrap">
                                    <label for="points" class="form-label">عدد النقاط لكل طالب <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="points" id="points" value="{{ old('points') }}" style="max-width: 240px;">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label d-block">نوع الاستهداف <span class="text-danger">*</span></label>
                                    @foreach($targetTypes as $value => $label)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input target-type-radio" type="radio" name="target_type" id="target_{{ $value }}" value="{{ $value }}" {{ $targetType === $value ? 'checked' : '' }}>
                                            <label class="form-check-label" for="target_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'single' ? '' : 'd-none' }}" data-target="single">
                                    <label for="user_id" class="form-label">الطالب</label>
                                    <select name="user_id" id="user_id" class="form-select student-search-select" style="width:100%"></select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'multiple' ? '' : 'd-none' }}" data-target="multiple">
                                    <label for="user_ids" class="form-label">الطلاب</label>
                                    <select name="user_ids[]" id="user_ids" class="form-select student-search-select-multiple" multiple style="width:100%"></select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'group' ? '' : 'd-none' }}" data-target="group">
                                    <label for="group_id_only" class="form-label">المجموعة</label>
                                    <select name="group_id" id="group_id_only" class="form-select">
                                        <option value="">اختر المجموعة</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'multiple_groups' ? '' : 'd-none' }}" data-target="multiple_groups">
                                    <label for="group_ids" class="form-label">المجموعات</label>
                                    <select name="group_ids[]" id="group_ids" class="form-select" multiple style="width:100%">
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ collect(old('group_ids', []))->contains($group->id) ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'course' ? '' : 'd-none' }}" data-target="course">
                                    <label for="course_id_only" class="form-label">الكورس</label>
                                    <select name="course_id" id="course_id_only" class="form-select">
                                        <option value="">اختر الكورس</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'course_group' ? '' : 'd-none' }}" data-target="course_group">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="course_id_grouped" class="form-label">الكورس</label>
                                            <select name="course_id" id="course_id_grouped" class="form-select">
                                                <option value="">اختر الكورس</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="group_id_course" class="form-label">المجموعة</label>
                                            <select name="group_id" id="group_id_course" class="form-select">
                                                <option value="">اختر المجموعة</option>
                                                @foreach($groups as $group)
                                                    <option
                                                        value="{{ $group->id }}"
                                                        data-course-ids="{{ $group->courses->pluck('id')->implode(',') }}"
                                                        {{ old('group_id') == $group->id ? 'selected' : '' }}
                                                    >{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label">السبب / الملاحظة <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="reason" id="reason" rows="3" required>{{ old('reason') }}</textarea>
                                </div>

                                <div class="alert alert-info d-none" id="points-preview-box"></div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-secondary" id="preview-recipients-btn">
                                        <i class="fas fa-eye me-1"></i> معاينة المستهدفين
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-coins me-1"></i> تنفيذ العملية
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('admin.pages.gamification.points.partials.grant-scripts')
@stop
