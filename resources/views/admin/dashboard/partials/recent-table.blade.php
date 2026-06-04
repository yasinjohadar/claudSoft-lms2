<div class="card custom-card dashboard-recent-table-card h-100">
    <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-start">
        <div>
            <h4 class="card-title mb-1">أحدث الشهادات / الاختبارات</h4>
            <span class="fs-12 text-muted">عرض مختصر لأحدث الشهادات الصادرة وأحدث محاولات الاختبارات.</span>
        </div>
        <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light bg-transparent rounded-pill" data-bs-toggle="dropdown">
            <i class="fe fe-more-horizontal"></i>
        </a>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('admin.certificates.index') }}">كل الشهادات</a>
            <a class="dropdown-item" href="{{ route('quizzes.index') }}">كل الاختبارات</a>
        </div>
    </div>
    <div class="card-body pt-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-nowrap dashboard-table">
                <thead>
                    <tr>
                        <th class="wd-lg-25p fs-12">الطالب</th>
                        <th class="wd-lg-25p fs-12">النوع</th>
                        <th class="wd-lg-25p fs-12">العنصر</th>
                        <th class="wd-lg-25p fs-12">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCertificates as $certificate)
                        <tr>
                            <td class="fs-13">{{ $certificate->user->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-warning-transparent">شهادة</span>
                            </td>
                            <td class="fw-medium fs-13">{{ $certificate->course->title ?? '-' }}</td>
                            <td class="text-muted fs-12">
                                {{ optional($certificate->created_at)->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    @foreach($recentQuizAttempts as $attempt)
                        <tr>
                            <td class="fs-13">{{ $attempt->student->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info-transparent">اختبار</span>
                            </td>
                            <td class="fw-medium fs-13">{{ $attempt->quiz->title ?? '-' }}</td>
                            <td class="text-muted fs-12">
                                {{ optional($attempt->created_at)->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @endforeach
                    @if($recentCertificates->isEmpty() && $recentQuizAttempts->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted fs-12 py-4">
                                لا توجد سجلات حديثة.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
