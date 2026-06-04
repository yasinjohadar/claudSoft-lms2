<div class="row mt-2">
    <div class="col-xl-6 col-lg-6 col-md-12 mb-3">
        <div class="card custom-card dashboard-timeline-card h-100">
            <div class="card-header border-0 pb-1">
                <h3 class="card-title mb-1 fs-16">أحدث الالتحاقات</h3>
                <p class="fs-12 mb-0 text-muted">آخر الطلاب المسجلين في الكورسات.</p>
            </div>
            <div class="product-timeline card-body pt-2 dashboard-timeline-body">
                <ul class="timeline-1 mb-0">
                    @forelse($recentEnrollments as $enrollment)
                        <li class="mt-0 dashboard-timeline-item">
                            <i class="fe fe-user-check bg-primary-transparent text-primary product-icon"></i>
                            <span class="fw-medium fs-14">
                                {{ $enrollment->student->name ?? 'طالب' }}
                            </span>
                            <span class="float-end fs-11 text-muted">
                                {{ optional($enrollment->enrollment_date)->diffForHumans() }}
                            </span>
                            <p class="mb-0 text-muted fs-12">
                                تم تسجيله في كورس
                                <strong>{{ $enrollment->course->title ?? '-' }}</strong>
                            </p>
                        </li>
                    @empty
                        <li class="mt-0 mb-0 text-muted fs-12">
                            لا توجد تسجيلات حديثة.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6 col-md-12 mb-3">
        <div class="card custom-card dashboard-webhook-card mb-3">
            <div class="card-header border-0 pb-0">
                <h3 class="card-title mb-1 fs-16">أحدث الأنشطة الآلية (n8n / Webhooks)</h3>
                <p class="fs-12 mb-0 text-muted">آخر عمليات التكامل مع الأنظمة الخارجية.</p>
            </div>
            <div class="card-body pt-2 pb-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th class="fs-12">الحدث</th>
                                <th class="fs-12">الحالة</th>
                                <th class="fs-12 text-end">التوقيت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWebhooks as $log)
                                <tr>
                                    <td class="fw-medium fs-13">
                                        {{ $log->event_name ?? $log->type ?? 'Webhook' }}
                                    </td>
                                    <td>
                                        @php
                                            $status = $log->status ?? 'unknown';
                                        @endphp
                                        <span class="badge bg-{{ $status === 'success' ? 'success' : ($status === 'failed' ? 'danger' : 'secondary') }}-transparent">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted fs-12">
                                        {{ optional($log->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted fs-12 py-4">
                                        لا توجد سجلات حديثة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card custom-card dashboard-quiz-progress-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2 pb-2">
                            <span class="avatar avatar-sm bg-primary-transparent">
                                <i class="fe fe-file-text text-primary"></i>
                            </span>
                            <p class="mb-0 fs-13">إجمالي المحاولات في الاختبارات</p>
                        </div>
                        <h4 class="fw-bold mb-2" data-countup="{{ $learningStats['quiz_attempts'] ?? 0 }}">0</h4>
                        <div class="progress progress-style progress-sm">
                            <div class="progress-bar bg-primary" style="width: 100%" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-4 mt-md-0">
                        <div class="d-flex align-items-center gap-2 pb-2">
                            <span class="avatar avatar-sm bg-success-transparent">
                                <i class="fe fe-check-circle text-success"></i>
                            </span>
                            <p class="mb-0 fs-13">المحاولات الناجحة ({{ $quizPassRate }}%)</p>
                        </div>
                        <h4 class="fw-bold mb-2" data-countup="{{ $learningStats['passed_attempts'] ?? 0 }}">0</h4>
                        <div class="progress progress-style progress-sm">
                            <div class="progress-bar bg-success" style="width: {{ $quizPassRate }}%" role="progressbar" aria-valuenow="{{ $quizPassRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-4 col-xl-4 mb-3">
        @include('admin.dashboard.partials.quick-numbers')
    </div>
    <div class="col-md-12 col-lg-8 col-xl-8 mb-3">
        @include('admin.dashboard.partials.recent-table')
    </div>
</div>
