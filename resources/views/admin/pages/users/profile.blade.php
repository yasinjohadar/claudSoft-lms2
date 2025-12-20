@extends('admin.layouts.master')

@section('page-title')
    ملف الطالب - {{ $user->name }}
@stop

@section('styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i> حدثت بعض الأخطاء:
                <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">ملف الطالب: {{ $user->name }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                        <li class="breadcrumb-item active">ملف الطالب</li>
                    </ol>
                </nav>
										</div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>تعديل البيانات
                </a>
                @if($user->hasRole('student'))
                    <a href="{{ route('admin.users.courses', $user->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-book me-1"></i>كورسات الطالب
                    </a>
                @endif
											</div>
										</div>

        <div class="row row-sm mt-3">
            <!-- Left: Basic info -->
            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xxl mb-3">
                                @if ($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle" style="object-fit: cover;">
                                @else
                                    <span class="avatar avatar-xxl bg-primary-gradient rounded-circle d-inline-flex align-items-center justify-content-center fs-30 text-white">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </span>
                                @endif
											</div>
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <span class="badge bg-{{ $user->hasRole('student') ? 'info' : 'secondary' }}">
                                {{ $user->roles->pluck('name')->join(' , ') ?: 'مستخدم' }}
                            </span>
											</div>
                        <hr>
                        <div class="mb-2">
                            <small class="text-muted d-block">البريد الإلكتروني</small>
                            <span><i class="fas fa-envelope me-1 text-muted"></i>{{ $user->email }}</span>
											</div>
                        @if($user->full_phone)
                            <div class="mb-2">
                                <small class="text-muted d-block">رقم الجوال</small>
                                <span><i class="fas fa-phone me-1 text-muted"></i>{{ $user->full_phone }}</span>
                                @if($user->whatsapp_url)
                                    <a href="{{ $user->whatsapp_url }}" target="_blank" class="badge bg-success ms-1">
                                        <i class="fab fa-whatsapp"></i> واتساب
                                    </a>
                                @endif
										</div>
                        @endif
                        @if($user->nationality)
                            <div class="mb-2">
                                <small class="text-muted d-block">الجنسية</small>
                                <span><i class="fas fa-flag me-1 text-muted"></i>{{ $user->nationality->name }}</span>
												</div>
                        @endif
                        <div class="mb-2">
                            <small class="text-muted d-block">الحالة</small>
                            @if($user->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-danger">موقوف</span>
                            @endif
												</div>
											</div>
										</div>
											</div>

            <!-- Right: Stats & Tabs -->
            <div class="col-xl-8">
                <div class="row row-sm mb-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card custom-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary-transparent me-3">
                                    <i class="fas fa-book fs-18 text-primary"></i>
										</div>
                                <div>
                                    <p class="mb-1 text-muted">إجمالي الكورسات</p>
                                    <h4 class="mb-0">{{ $courseStats['total_enrollments'] }}</h4>
								</div>
							</div>
						</div>
					</div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card custom-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar avatar-md bg-success-transparent me-3">
                                    <i class="fas fa-play-circle fs-18 text-success"></i>
											</div>
                                <div>
                                    <p class="mb-1 text-muted">كورسات نشطة</p>
                                    <h4 class="mb-0">{{ $courseStats['active_enrollments'] }}</h4>
											</div>
										</div>
									</div>
								</div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card custom-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar avatar-md bg-info-transparent me-3">
                                    <i class="fas fa-check-circle fs-18 text-info"></i>
							</div>
                                <div>
                                    <p class="mb-1 text-muted">كورسات مكتملة</p>
                                    <h4 class="mb-0">{{ $courseStats['completed_enrollments'] }}</h4>
											</div>
										</div>
									</div>
								</div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card custom-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="avatar avatar-md bg-warning-transparent me-3">
                                    <i class="fas fa-percentage fs-18 text-warning"></i>
							</div>
                                <div>
                                    <p class="mb-1 text-muted">متوسط التقدم</p>
                                    <h4 class="mb-0">{{ number_format($courseStats['average_progress'], 1) }}%</h4>
											</div>
										</div>
									</div>
								</div>
							</div>

                <div class="card custom-card">
							<div class="card-body">
									<ul class="nav nav-tabs profile navtab-custom panel-tabs">
                            <li class="nav-item">
                                <a href="#tab-overview" class="nav-link active" data-bs-toggle="tab">
                                    <i class="las la-user-circle me-1"></i> بيانات الطالب
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-courses" class="nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-book me-1"></i> الكورسات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#tab-quizzes" class="nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-clipboard-list me-1"></i> الاختبارات
                                </a>
										</li>
                            <li class="nav-item">
                                <a href="#tab-billing" class="nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-file-invoice-dollar me-1"></i> الفواتير / المدفوعات
                                </a>
										</li>
                            <li class="nav-item">
                                <a href="#tab-certificates" class="nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-certificate me-1"></i> الشهادات
                                </a>
										</li>
                            <li class="nav-item">
                                <a href="#tab-groups" class="nav-link" data-bs-toggle="tab">
                                    <i class="fas fa-users me-1"></i> المجموعات
                                </a>
										</li>
									</ul>

								<div class="tab-content border border-top-0 p-4 br-dark">
                            <!-- Overview -->
                            <div class="tab-pane active" id="tab-overview">
                                <h5 class="mb-3">ملخص سريع</h5>
                                <p class="text-muted">
                                    يمكن من خلال هذه الصفحة متابعة كل ما يخص الطالب من كورسات، اختبارات، مدفوعات وشهادات في مكان واحد.
                                </p>
											</div>

                            <!-- Courses -->
                            <div class="tab-pane" id="tab-courses">
                                @if($enrollments->isEmpty())
                                    <p class="text-muted mb-0">لا توجد كورسات مسجلة لهذا الطالب.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الكورس</th>
                                                <th>الحالة</th>
                                                <th>نسبة الإكمال</th>
                                                <th>تاريخ التسجيل</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($enrollments as $enrollment)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $enrollment->course->title ?? '-' }}</td>
                                                    <td>{{ $enrollment->enrollment_status }}</td>
                                                    <td>{{ $enrollment->completion_percentage }}%</td>
                                                    <td>{{ optional($enrollment->enrollment_date)->format('Y-m-d') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
											</div>
                                @endif
										</div>

                            <!-- Quizzes -->
                            <div class="tab-pane" id="tab-quizzes">
                                @if($quizAttempts->isEmpty())
                                    <p class="text-muted mb-0">لا توجد محاولات اختبارات مسجلة.</p>
                                @else
                                    <p class="mb-3">
                                        إجمالي المحاولات: <strong>{{ $quizStats['total_attempts'] }}</strong> -
                                        متوسط الدرجة: <strong>{{ number_format($quizStats['average_score'], 1) }}%</strong>
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الاختبار</th>
                                                <th>الحالة</th>
                                                <th>الدرجة</th>
                                                <th>تاريخ الإكمال</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($quizAttempts as $attempt)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $attempt->quiz->title ?? '-' }}</td>
                                                    <td>{{ $attempt->status }}</td>
                                                    <td>{{ $attempt->percentage_score }}%</td>
                                                    <td>{{ optional($attempt->completed_at ?? $attempt->submitted_at)->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
									</div>
                                @endif
												</div>

                            <!-- Billing -->
                            <div class="tab-pane" id="tab-billing">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 text-center">
                                            <small class="text-muted d-block">عدد الفواتير</small>
                                            <strong>{{ $billingStats['total_invoices'] }}</strong>
												</div>
											</div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 text-center">
                                            <small class="text-muted d-block">إجمالي المبلغ</small>
                                            <strong>{{ number_format($billingStats['total_amount'], 2) }}</strong>
												</div>
											</div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 text-center">
                                            <small class="text-muted d-block">المدفوع</small>
                                            <strong>{{ number_format($billingStats['total_paid'], 2) }}</strong>
												</div>
											</div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 text-center">
                                            <small class="text-muted d-block">المتبقي</small>
                                            <strong>{{ number_format($billingStats['remaining_amount'], 2) }}</strong>
												</div>
											</div>
										</div>

                                <h6 class="mb-2">آخر الفواتير</h6>
                                @if($invoices->isEmpty())
                                    <p class="text-muted mb-0">لا توجد فواتير لهذا الطالب.</p>
                                @else
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>رقم الفاتورة</th>
                                                <th>التاريخ</th>
                                                <th>الإجمالي</th>
                                                <th>المدفوع</th>
                                                <th>الحالة</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($invoices as $invoice)
                                                <tr>
                                                    <td>{{ $invoice->invoice_number }}</td>
                                                    <td>{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
                                                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                                    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                                    <td>{{ $invoice->status }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
									</div>
                                @endif

                                <h6 class="mb-2 mt-3">آخر المدفوعات</h6>
                                @if($payments->isEmpty())
                                    <p class="text-muted mb-0">لا توجد مدفوعات مسجلة.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>رقم الدفعة</th>
                                                <th>التاريخ</th>
                                                <th>المبلغ</th>
                                                <th>طريقة الدفع</th>
                                                <th>الحالة</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_number }}</td>
                                                    <td>{{ optional($payment->payment_date)->format('Y-m-d H:i') }}</td>
                                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ $payment->paymentMethod->name ?? '-' }}</td>
                                                    <td>{{ $payment->status }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
															</div>
                                @endif
														</div>

                            <!-- Certificates -->
                            <div class="tab-pane" id="tab-certificates">
                                @if($certificates->isEmpty())
                                    <p class="text-muted mb-0">لا توجد شهادات صادرة لهذا الطالب.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>الكورس</th>
                                                <th>تاريخ الإصدار</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($certificates as $certificate)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $certificate->course->title ?? '-' }}</td>
                                                    <td>{{ optional($certificate->created_at)->format('Y-m-d') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
															</div>
                                @endif
													</div>

                            <!-- Groups -->
                            <div class="tab-pane" id="tab-groups">
                                @if($groups->isEmpty())
                                    <p class="text-muted mb-0">لا توجد مجموعات مسجل بها هذا الطالب.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>اسم المجموعة</th>
                                                <th>الدور</th>
                                                <th>الكورس المرتبط</th>
                                                <th>تاريخ الانضمام</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($groups as $member)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $member->group->name ?? '-' }}</td>
                                                    <td>
                                                        @if($member->role === 'leader')
                                                            <span class="badge bg-purple">قائد المجموعة</span>
                                                        @else
                                                            <span class="badge bg-secondary">عضو</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $course = optional($member->group)->courses->first(); @endphp
                                                        {{ $course->title ?? '-' }}
                                                    </td>
                                                    <td>{{ optional($member->joined_at)->format('Y-m-d') }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
												</div>
                                @endif
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

    </div>
    </div>
@endsection


