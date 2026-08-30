@if (($activeCampMemberships ?? collect())->isNotEmpty())
    @php
        $campCount = $activeCampMemberships->count();
        $campColumnClass = $campCount >= 3
            ? 'col-xl-3 col-lg-4 col-md-6 col-sm-12'
            : 'col-xl-4 col-lg-4 col-md-6 col-sm-12';
    @endphp

    {{--
        نفس ترويسة قسم «روابط سريعة» (shortcuts-section) بدل بطاقة card بيضاء،
        والغلاف .hr-stat-widgets إلزامي لترث بطاقات المعسكرات طبقاتها الزخرفية.
    --}}
    <div class="shortcuts-section mb-4">
        <div class="shortcuts-section-header">
            <span class="shortcuts-section-icon"><i class="ri-flag-line"></i></span>
            <div>
                <h5 class="dashboard-section-title mb-0">معسكراتي</h5>
                <p class="text-muted fs-12 mb-0">متابعة معسكراتك المسجّلة والأيام المتبقية</p>
            </div>
        </div>
        <div class="row g-3 hr-stat-widgets">
            @foreach ($activeCampMemberships as $campIndex => $membership)
                @include('student.dashboard.partials.camp-countdown-widget', [
                    'membership' => $membership,
                    'columnClass' => $campColumnClass,
                    'staggerDelay' => $campIndex * 100,
                ])
            @endforeach
        </div>
    </div>
@endif
