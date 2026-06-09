@if($canRequest)
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-header border-0 pb-0">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-success-transparent">
                    <i class="fe fe-user-plus text-success"></i>
                </span>
                <div>
                    <h6 class="card-title mb-0">طلب الانضمام</h6>
                    <p class="text-muted fs-12 mb-0">أرسل طلبك للإدارة للمراجعة</p>
                </div>
            </div>
        </div>
        <div class="card-body pt-3">
            <form action="{{ route('student.groups.request', $group->id) }}" method="POST" class="student-group-join-form">
                @csrf

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input @error('terms_accepted') is-invalid @enderror"
                               type="checkbox"
                               name="terms_accepted"
                               value="1"
                               id="terms_accepted"
                               required>
                        <label class="form-check-label" for="terms_accepted">
                            أوافق على شروط المعسكر
                        </label>
                        @error('terms_accepted')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-12 fw-semibold">متى يمكنك تسديد رسوم المعسكر؟</label>
                    <input type="date"
                           name="payment_date"
                           class="form-control @error('payment_date') is-invalid @enderror"
                           min="{{ date('Y-m-d') }}">
                    @error('payment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">تاريخ تقديري تقريبي (اختياري)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-12 fw-semibold">رسالة للإدارة <span class="text-danger">*</span></label>
                    <textarea name="message"
                              class="form-control @error('message') is-invalid @enderror"
                              rows="5"
                              placeholder="يرجى كتابة:
- وسيلة الدفع التي تفضل استخدامها
- أي ملاحظات أو معلومات إضافية للإدارة"
                              required></textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">يرجى كتابة وسيلة الدفع المفضلة وأي ملاحظات إضافية للإدارة.</div>
                </div>

                <button type="submit" class="btn btn-success rounded-pill w-100">
                    <i class="fe fe-send me-1"></i>إرسال طلب الانضمام
                </button>
            </form>
        </div>
    </div>
@elseif($hasPendingRequest)
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-body text-center py-5">
            <div class="student-my-courses-empty__icon mb-3 mx-auto">
                <i class="fe fe-clock"></i>
            </div>
            <h5 class="mb-2">طلب قيد المراجعة</h5>
            <p class="text-muted mb-4">لديك طلب انضمام قيد المراجعة لهذه المجموعة.</p>
            <a href="{{ route('student.groups.my-requests') }}" class="btn btn-outline-primary rounded-pill">
                <i class="fe fe-list me-1"></i>عرض طلباتي
            </a>
        </div>
    </div>
@elseif($group->hasMember(auth()->user()))
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-body text-center py-5">
            <div class="student-my-courses-empty__icon mb-3 mx-auto" style="background: rgba(25, 135, 84, 0.12); color: #198754;">
                <i class="fe fe-check-circle"></i>
            </div>
            <h5 class="mb-2">عضو في المجموعة</h5>
            <p class="text-muted mb-0">أنت عضو في هذه المجموعة بالفعل.</p>
        </div>
    </div>
@else
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-body text-center py-5">
            <div class="student-my-courses-empty__icon mb-3 mx-auto">
                <i class="fe fe-slash"></i>
            </div>
            <h5 class="mb-2">غير متاح</h5>
            <p class="text-muted mb-0">طلب الانضمام غير متاح لهذه المجموعة حالياً.</p>
        </div>
    </div>
@endif
