@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الشهادة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل الشهادة {{ $certificate->certificate_number }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">الشهادات</a></li>
                            <li class="breadcrumb-item active">التفاصيل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Certificate Info -->
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات الشهادة</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">رقم الشهادة</th>
                                        <td>
                                            <strong class="text-primary">{{ $certificate->certificate_number }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>اسم الطالب</th>
                                        <td>{{ $certificate->student_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>اسم الكورس</th>
                                        <td>{{ $certificate->course_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>القالب المستخدم</th>
                                        <td>{{ $certificate->template->name ?? 'غير محدد' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإصدار</th>
                                        <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإكمال</th>
                                        <td>{{ $certificate->completion_date ? $certificate->completion_date->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الانتهاء</th>
                                        <td>
                                            @if($certificate->expiry_date)
                                                {{ $certificate->expiry_date->format('Y-m-d') }}
                                                @if($certificate->expiry_date->isPast())
                                                    <span class="badge bg-danger ms-2">منتهية</span>
                                                @endif
                                            @else
                                                <span class="badge bg-success">غير محددة (صالحة دائماً)</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>الحالة</th>
                                        <td>
                                            @if($certificate->status == 'active')
                                                <span class="badge bg-success fs-14">نشطة</span>
                                            @elseif($certificate->status == 'revoked')
                                                <span class="badge bg-danger fs-14">ملغاة</span>
                                            @else
                                                <span class="badge bg-warning fs-14">منتهية</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($certificate->status == 'revoked')
                                        <tr>
                                            <th>سبب الإلغاء</th>
                                            <td class="text-danger">{{ $certificate->revocation_reason }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإلغاء</th>
                                            <td>{{ $certificate->revoked_at?->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Student Performance -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">أداء الطالب</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-check-circle fs-30 text-success mb-2"></i>
                                        <h4 class="mb-1">{{ $certificate->completion_percentage ?? 0 }}%</h4>
                                        <small class="text-muted">نسبة الإكمال</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-user-check fs-30 text-info mb-2"></i>
                                        <h4 class="mb-1">{{ $certificate->attendance_percentage ?? 0 }}%</h4>
                                        <small class="text-muted">نسبة الحضور</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-graduation-cap fs-30 text-warning mb-2"></i>
                                        <h4 class="mb-1">{{ $certificate->final_exam_score ?? '-' }}</h4>
                                        <small class="text-muted">درجة الاختبار النهائي</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions & QR Code -->
                <div class="col-xl-4">
                    <!-- QR Code -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">رمز التحقق (QR Code)</div>
                        </div>
                        <div class="card-body text-center">
                            @if($certificate->qr_code_path)
                                <img src="{{ asset('storage/' . $certificate->qr_code_path) }}"
                                     alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                                <p class="text-muted small">
                                    <i class="fas fa-link me-1"></i>
                                    <a href="{{ $certificate->verification_url }}" target="_blank">
                                        {{ $certificate->verification_url }}
                                    </a>
                                </p>
                            @else
                                <p class="text-muted">لا يوجد رمز QR</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الإجراءات</div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($certificate->status == 'active' && $certificate->pdf_path)
                                    <a href="{{ route('admin.certificates.download', $certificate->id) }}"
                                       class="btn btn-success">
                                        <i class="fas fa-download me-2"></i>تحميل PDF
                                    </a>
                                @endif

                                @if($certificate->status == 'active')
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#revokeModal">
                                        <i class="fas fa-ban me-2"></i>إلغاء الشهادة
                                    </button>
                                @endif

                                <form action="{{ route('admin.certificates.reissue', $certificate->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100"
                                            onclick="return confirm('هل تريد إعادة إصدار هذه الشهادة؟')">
                                        <i class="fas fa-redo me-2"></i>إعادة الإصدار
                                    </button>
                                </form>

                                <form action="{{ route('admin.certificates.destroy', $certificate->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100"
                                            onclick="return confirm('هل أنت متأكد من حذف هذه الشهادة؟')">
                                        <i class="fas fa-trash me-2"></i>حذف الشهادة
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إحصائيات</div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>عدد التحميلات:</span>
                                <strong>{{ $certificate->download_count }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>آخر تحميل:</span>
                                <strong>{{ $certificate->last_downloaded_at ? $certificate->last_downloaded_at->diffForHumans() : 'لم يتم التحميل بعد' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>تم الإصدار بواسطة:</span>
                                <strong>{{ $certificate->issuedBy->name ?? 'النظام' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Revoke Modal -->
    <div class="modal fade" id="revokeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.certificates.revoke', $certificate->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إلغاء الشهادة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">سبب الإلغاء <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="4" required
                                      placeholder="اذكر سبب إلغاء الشهادة..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
