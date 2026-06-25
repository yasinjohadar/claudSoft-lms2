@extends('admin.layouts.master')

@section('page-title')
    معاينة السؤال
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.page-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-take.css') }}?v={{ @filemtime(public_path('assets/css/quiz-take.css')) ?: '1' }}">
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid px-3 px-lg-4">
            @include('admin.components.alerts')

            <div class="admin-show-layout">
                <div class="my-4 page-header-breadcrumb qb-page-animate dashboard-fade-in">
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-bank.show', $question->id) }}">تفاصيل السؤال</a></li>
                            <li class="breadcrumb-item active">معاينة</li>
                        </ol>
                    </nav>
                </div>

                <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
                    <div class="row align-items-start g-3">
                        <div class="col-lg-8">
                            <span class="group-show-hero__eyebrow"><i class="fe fe-eye me-1"></i>معاينة السؤال</span>
                            <h2 class="group-show-hero__title mb-2">{{ Str::limit(strip_tags($question->question_text), 90) }}</h2>
                            <p class="group-show-hero__desc mb-0">كيف سيظهر السؤال للطالب (للقراءة فقط)</p>
                        </div>
                        <div class="col-lg-4">
                            <div class="group-show-actions">
                                <a href="{{ route('question-bank.show', $question->id) }}" class="group-show-action">
                                    <span class="group-show-action__icon"><i class="fe fe-file-text"></i></span>
                                    <span class="group-show-action__text">تفاصيل السؤال</span>
                                </a>
                                <a href="{{ route('question-bank.edit', $question->id) }}" class="group-show-action group-show-action--primary">
                                    <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                    <span class="group-show-action__text">تعديل</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate quiz-take-page">
                    <div class="card-body pt-3 p-md-4">
                        @include('admin.pages.question-bank.partials.preview', ['question' => $question])
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
