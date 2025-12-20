
<div class="courses">
  <div class="all-courses container">
    <div class="d-flex justify-content-between">
        <h2 class="mb-4"> الكورسات:</h2>
        <a href="{{ route('frontend.courses.index') }}">عرض المزيد </a>
    </div>
    <div class="inner-courses row row-cols-2 row-cols-sm-2 row-cols-md-4 text-center">

        @forelse($courses as $course)
        <div class="course col">
            <a href="{{ route('frontend.courses.show', $course->slug) }}" class="course-link">
                <div class="course-img">
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                </div>
                <div class="course-info">
                    <h3>{{ $course->title }}</h3>
                    <p>
                       {{ Str::limit($course->subtitle, 50) }}
                    </p>
                    <hr>

                    @if($course->is_free)
                    <div class="course-price">
                        <div class="price">
                            <span style="color: #38c172; font-weight: bold; font-size: 1.2em;">مجاني</span>
                        </div>
                    </div>
                    @else
                    <div class="course-price">
                        <div class="price">
                            <span>سعر الكورس:</span>
                            @if($course->has_discount)
                                <span style="text-decoration: line-through;">{{ $course->price }}{{ $course->currency }}</span>
                            @else
                                <span>{{ $course->price }}{{ $course->currency }}</span>
                            @endif
                        </div>
                        @if($course->has_discount)
                            <span>{{ $course->discount_price }}{{ $course->currency }}</span>
                        @endif
                    </div>
                    @endif

                    <button class="course-details">تفاصيل الكورس</button>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center">
            <p>لا توجد كورسات متاحة حالياً</p>
        </div>
        @endforelse

    </div>
  </div>
</div>
