@extends('student.layouts.master')

@section('page-title')
    معرض أعمالي (Portfolio)
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Hero Section -->
            <div class="card custom-card bg-primary text-white mb-4">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xxl bg-white-transparent mb-3">
                        <i class="ri-gallery-line fs-40"></i>
                    </div>
                    <h2 class="text-white mb-2">معرض أعمالي</h2>
                    <p class="text-white-50 mb-3">استعراض مشاريعي وإنجازاتي المعتمدة</p>
                    <a href="{{ route('student.works.index') }}" class="btn btn-light">
                        <i class="ri-arrow-right-line me-1"></i>إدارة جميع الأعمال
                    </a>
                </div>
            </div>

            <!-- Featured Works -->
            @if($featured->count() > 0)
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0"><i class="ri-star-fill text-warning me-2"></i>الأعمال المميزة</h5>
                    </div>

                    <div class="row">
                        @foreach($featured as $work)
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card custom-card border-warning">
                                    <div class="position-relative">
                                        @if($work->image)
                                            <img src="{{ $work->image_url }}" class="card-img-top" alt="{{ $work->title }}" style="height: 250px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                                @php
                                                    $categories = \App\Models\StudentWork::getCategories();
                                                @endphp
                                                <i class="{{ $categories[$work->category]['icon'] }} fs-50 text-muted opacity-25"></i>
                                            </div>
                                        @endif
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-2">
                                            <i class="ri-star-fill me-1"></i>مميز
                                        </span>
                                    </div>

                                    <div class="card-body">
                                        @php
                                            $categories = \App\Models\StudentWork::getCategories();
                                        @endphp
                                        <span class="badge bg-{{ $categories[$work->category]['color'] }}-transparent mb-2">
                                            <i class="{{ $categories[$work->category]['icon'] }} me-1"></i>
                                            {{ $categories[$work->category]['name'] }}
                                        </span>

                                        <h5 class="card-title mb-2">{{ $work->title }}</h5>

                                        @if($work->description)
                                            <p class="text-muted mb-3" style="font-size: 13px;">
                                                {{ Str::limit($work->description, 120) }}
                                            </p>
                                        @endif

                                        @if($work->rating)
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="ri-star-fill text-warning me-1"></i>
                                                <strong>{{ $work->rating }}</strong>
                                                <span class="text-muted ms-1">/ 10</span>
                                            </div>
                                        @endif

                                        <div class="d-flex gap-2 mb-3">
                                            @if($work->github_url)
                                                <a href="{{ $work->github_url }}" target="_blank" class="btn btn-sm btn-outline-dark flex-fill">
                                                    <i class="ri-github-fill"></i>
                                                </a>
                                            @endif
                                            @if($work->demo_url)
                                                <a href="{{ $work->demo_url }}" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                                    <i class="ri-earth-line me-1"></i>Demo
                                                </a>
                                            @endif
                                        </div>

                                        <a href="{{ route('student.works.show', $work) }}" class="btn btn-primary w-100">
                                            <i class="ri-eye-line me-1"></i>عرض التفاصيل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- All Works -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0"><i class="ri-folder-user-line me-2"></i>جميع الأعمال</h5>
                <span class="badge bg-primary-transparent">{{ $works->count() }} عمل</span>
            </div>

            @if($works->count() > 0)
                <div class="row">
                    @foreach($works as $work)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card">
                                @if($work->image)
                                    <img src="{{ $work->image_url }}" class="card-img-top" alt="{{ $work->title }}" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        @php
                                            $categories = \App\Models\StudentWork::getCategories();
                                        @endphp
                                        <i class="{{ $categories[$work->category]['icon'] }} fs-50 text-muted opacity-25"></i>
                                    </div>
                                @endif

                                <div class="card-body">
                                    @php
                                        $categories = \App\Models\StudentWork::getCategories();
                                    @endphp
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-{{ $categories[$work->category]['color'] }}-transparent">
                                            <i class="{{ $categories[$work->category]['icon'] }} me-1"></i>
                                            {{ $categories[$work->category]['name'] }}
                                        </span>
                                        @if($work->is_featured)
                                            <span class="badge bg-warning">
                                                <i class="ri-star-fill"></i>
                                            </span>
                                        @endif
                                    </div>

                                    <h6 class="card-title mb-2">
                                        <a href="{{ route('student.works.show', $work) }}" class="text-dark">
                                            {{ $work->title }}
                                        </a>
                                    </h6>

                                    @if($work->course)
                                        <p class="text-muted mb-2" style="font-size: 12px;">
                                            <i class="ri-book-line me-1"></i>{{ $work->course->title }}
                                        </p>
                                    @endif

                                    @if($work->description)
                                        <p class="text-muted mb-3" style="font-size: 12px;">
                                            {{ Str::limit($work->description, 80) }}
                                        </p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        @if($work->completion_date)
                                            <small class="text-muted">
                                                <i class="ri-calendar-line me-1"></i>{{ $work->completion_date->format('Y/m/d') }}
                                            </small>
                                        @else
                                            <span></span>
                                        @endif
                                        @if($work->rating)
                                            <span class="badge bg-success-transparent">
                                                <i class="ri-star-fill me-1"></i>{{ $work->rating }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($work->technologies)
                                        <div class="mb-3">
                                            @foreach(array_slice(explode(',', $work->technologies), 0, 3) as $tech)
                                                <span class="badge bg-light text-dark me-1" style="font-size: 10px;">
                                                    {{ trim($tech) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.works.show', $work) }}" class="btn btn-sm btn-primary flex-fill">
                                            <i class="ri-eye-line me-1"></i>عرض
                                        </a>
                                        @if($work->demo_url)
                                            <a href="{{ $work->demo_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-external-link-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <i class="ri-folder-user-line fs-50 text-muted mb-3 opacity-25"></i>
                        <h5 class="mb-2">لا توجد أعمال معتمدة بعد</h5>
                        <p class="text-muted mb-4">قم بإضافة أعمالك وإرسالها للمراجعة لتظهر في معرضك</p>
                        <a href="{{ route('student.works.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-2"></i>إضافة عمل جديد
                        </a>
                    </div>
                </div>
            @endif

            <!-- Statistics -->
            @if($works->count() > 0)
                <div class="card custom-card mt-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="ri-bar-chart-box-line me-2"></i>إحصائيات البورتفوليو
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-primary-transparent rounded">
                                    <h3 class="text-primary mb-1">{{ $works->count() }}</h3>
                                    <p class="text-muted mb-0">إجمالي الأعمال</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-warning-transparent rounded">
                                    <h3 class="text-warning mb-1">{{ $featured->count() }}</h3>
                                    <p class="text-muted mb-0">الأعمال المميزة</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-success-transparent rounded">
                                    <h3 class="text-success mb-1">{{ $works->sum('views_count') }}</h3>
                                    <p class="text-muted mb-0">إجمالي المشاهدات</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="p-3 bg-info-transparent rounded">
                                    <h3 class="text-info mb-1">
                                        @php
                                            $avgRating = $works->whereNotNull('rating')->avg('rating');
                                        @endphp
                                        {{ $avgRating ? number_format($avgRating, 1) : '-' }}
                                    </h3>
                                    <p class="text-muted mb-0">متوسط التقييم</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Distribution -->
                @php
                    $categoriesCount = [];
                    $categories = \App\Models\StudentWork::getCategories();
                    foreach($works as $work) {
                        if(!isset($categoriesCount[$work->category])) {
                            $categoriesCount[$work->category] = 0;
                        }
                        $categoriesCount[$work->category]++;
                    }
                @endphp

                @if(count($categoriesCount) > 0)
                    <div class="card custom-card mt-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="ri-pie-chart-line me-2"></i>توزيع الأعمال حسب التصنيف
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($categoriesCount as $categoryKey => $count)
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md bg-{{ $categories[$categoryKey]['color'] }}-transparent me-3">
                                                <i class="{{ $categories[$categoryKey]['icon'] }} text-{{ $categories[$categoryKey]['color'] }}"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $categories[$categoryKey]['name'] }}</h6>
                                                <small class="text-muted">{{ $count }} {{ $count == 1 ? 'عمل' : 'أعمال' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>
@stop

@section('script')
<script>
    // Portfolio animations or interactions can be added here
</script>
@stop
