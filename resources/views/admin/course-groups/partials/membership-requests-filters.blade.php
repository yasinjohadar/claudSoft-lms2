@php
    use App\Support\MembershipRequestFilters;

    $formFilters = MembershipRequestFilters::formFilterDefinitions();
    $sortOptions = MembershipRequestFilters::sortOptions();
    $activeFilterCount = MembershipRequestFilters::activeFilterCount(request());
    $emailStats = $emailStats ?? ['not_invited' => 0, 'invite_sent' => 0, 'no_email' => 0];
    $showAdvanced = $activeFilterCount > 0
        || request()->hasAny(array_merge(array_keys($formFilters), ['other_groups', 'request_from', 'request_to', 'payment_from', 'payment_to', 'sort_by', 'sort_order', 'per_page', 'email_invite']));
@endphp

<form id="membershipRequestsFilterForm" method="GET"
      action="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}"
      class="group-show-filters mb-0">
    <div class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-4">
            <label class="form-label">البحث</label>
            <input type="text"
                   id="membershipRequestsSearchInput"
                   name="search"
                   class="form-control js-mr-filter js-mr-filter-text"
                   placeholder="الاسم، الإيميل، الهاتف، أو حقول الفورم..."
                   value="{{ request('search') }}">
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select js-mr-filter">
                <option value="">جميع الحالات</option>
                <option value="pending" @selected(request('status') === 'pending')>قيد المراجعة</option>
                <option value="approved" @selected(request('status') === 'approved')>مقبول</option>
                <option value="rejected" @selected(request('status') === 'rejected')>مرفوض</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label">مجموعات أخرى</label>
            <select name="other_groups" class="form-select js-mr-filter">
                <option value="">الكل</option>
                <option value="yes" @selected(request('other_groups') === 'yes')>منضم لمجموعات أخرى</option>
                <option value="no" @selected(request('other_groups') === 'no')>غير منضم لمجموعات أخرى</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-3">
            <label class="form-label">دعوة البريد</label>
            <select name="email_invite" class="form-select js-mr-filter" id="membershipEmailInviteFilter">
                <option value="">جميع الطلاب</option>
                <option value="not_invited" @selected(request('email_invite') === 'not_invited')>لم يُدعَ ({{ $emailStats['not_invited'] ?? 0 }})</option>
                <option value="invite_sent" @selected(request('email_invite') === 'invite_sent')>دُعوا بالبريد ({{ $emailStats['invite_sent'] ?? 0 }})</option>
                <option value="no_email" @selected(request('email_invite') === 'no_email')>بدون بريد ({{ $emailStats['no_email'] ?? 0 }})</option>
            </select>
        </div>
        @if($waSelectedJid ?? '')
            <div class="col-lg-2 col-md-3">
                <label class="form-label">انضمام واتساب</label>
                <select name="wa_membership" class="form-select js-mr-filter" id="membershipWaMembershipFilter">
                    <option value="">جميع الطلاب</option>
                    <option value="not_in_group" @selected(request('wa_membership') === 'not_in_group')>غير منضمين ({{ $waContext['wa_stats']['not_in_group'] ?? 0 }})</option>
                    <option value="invite_sent" @selected(request('wa_membership') === 'invite_sent')>دُعوا ولم ينضموا ({{ $waContext['wa_stats']['invite_pending'] ?? 0 }})</option>
                    <option value="in_group" @selected(request('wa_membership') === 'in_group')>منضمين ({{ $waContext['wa_stats']['in_group'] ?? 0 }})</option>
                    <option value="no_phone" @selected(request('wa_membership') === 'no_phone')>بدون رقم ({{ $waContext['wa_stats']['no_phone'] ?? 0 }})</option>
                </select>
            </div>
            <input type="hidden" name="whatsapp_jid" value="{{ $waSelectedJid }}">
        @endif
        <div class="col-lg-2 col-md-3">
            <label class="form-label">ترتيب حسب</label>
            <select name="sort_by" class="form-select js-mr-filter">
                @foreach($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('sort_by', 'created_at') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-1 col-md-2">
            <label class="form-label">الاتجاه</label>
            <select name="sort_order" class="form-select js-mr-filter">
                <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>تنازلي</option>
                <option value="asc" @selected(request('sort_order') === 'asc')>تصاعدي</option>
            </select>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="collapse"
                data-bs-target="#membershipAdvancedFilters"
                aria-expanded="{{ $showAdvanced ? 'true' : 'false' }}"
                aria-controls="membershipAdvancedFilters">
            <i class="fe fe-sliders me-1"></i>فلاتر متقدمة
            @if($activeFilterCount > 0)
                <span class="badge bg-primary ms-1">{{ $activeFilterCount }}</span>
            @endif
        </button>
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fe fe-search me-1"></i>تطبيق
        </button>
        <button type="button" id="membershipRequestsResetBtn" class="btn btn-sm btn-outline-secondary">
            <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
        </button>
        <select name="per_page" class="form-select form-select-sm js-mr-filter" style="width: auto;">
            @foreach([15, 25, 50, 100] as $size)
                <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }} / صفحة</option>
            @endforeach
        </select>
    </div>

    <div class="collapse {{ $showAdvanced ? 'show' : '' }} mt-3" id="membershipAdvancedFilters">
        <div class="border rounded-3 p-3 bg-light-subtle">
            <p class="fs-12 fw-semibold text-muted mb-2">تواريخ الطلب والرسوم</p>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label fs-12">تاريخ الطلب من</label>
                    <input type="date" name="request_from" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('request_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12">تاريخ الطلب إلى</label>
                    <input type="date" name="request_to" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('request_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12">موعد الرسوم من</label>
                    <input type="date" name="payment_from" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('payment_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12">موعد الرسوم إلى</label>
                    <input type="date" name="payment_to" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('payment_to') }}">
                </div>
            </div>

            <p class="fs-12 fw-semibold text-muted mb-2 border-top pt-3">بيانات الفورم — شخصية</p>
            <div class="row g-3 mb-3">
                @foreach(['reg_name', 'reg_name_ar', 'reg_city'] as $key)
                    @php $def = $formFilters[$key]; @endphp
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label fs-12">{{ $def['label'] }}</label>
                        <input type="text" name="{{ $key }}" class="form-control form-control-sm js-mr-filter js-mr-filter-text"
                               placeholder="{{ $def['placeholder'] ?? '' }}"
                               value="{{ request($key) }}">
                    </div>
                @endforeach
                <div class="col-md-4 col-xl-3">
                    <label class="form-label fs-12">{{ $formFilters['reg_nationality_id']['label'] }}</label>
                    <select name="reg_nationality_id" class="form-select form-select-sm js-mr-filter">
                        <option value="">الكل</option>
                        @foreach($nationalities ?? [] as $nationality)
                            <option value="{{ $nationality->id }}" @selected((string) request('reg_nationality_id') === (string) $nationality->id)>
                                {{ $nationality->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-xl-3">
                    <label class="form-label fs-12">{{ $formFilters['reg_gender']['label'] }}</label>
                    <select name="reg_gender" class="form-select form-select-sm js-mr-filter">
                        <option value="">الكل</option>
                        @foreach($formFilters['reg_gender']['options'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('reg_gender') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-xl-3">
                    <label class="form-label fs-12">{{ $formFilters['reg_dob_from']['label'] }}</label>
                    <input type="date" name="reg_dob_from" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('reg_dob_from') }}">
                </div>
                <div class="col-md-4 col-xl-3">
                    <label class="form-label fs-12">{{ $formFilters['reg_dob_to']['label'] }}</label>
                    <input type="date" name="reg_dob_to" class="form-control form-control-sm js-mr-filter"
                           value="{{ request('reg_dob_to') }}">
                </div>
                <div class="col-md-4 col-xl-3">
                    <label class="form-label fs-12">{{ $formFilters['reg_has_form']['label'] }}</label>
                    <select name="reg_has_form" class="form-select form-select-sm js-mr-filter">
                        <option value="">الكل</option>
                        @foreach($formFilters['reg_has_form']['options'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('reg_has_form') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <p class="fs-12 fw-semibold text-muted mb-2 border-top pt-3">بيانات الفورم — خبرة وتعليم</p>
            <div class="row g-3">
                @foreach(['reg_has_computer', 'reg_commitment', 'reg_sufficient_time', 'reg_bootcamp'] as $key)
                    @php $def = $formFilters[$key]; @endphp
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label fs-12">{{ $def['label'] }}</label>
                        <select name="{{ $key }}" class="form-select form-select-sm js-mr-filter">
                            <option value="">الكل</option>
                            @foreach($def['options'] as $val => $label)
                                <option value="{{ $val }}" @selected(request($key) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                @foreach(['reg_computer_exp', 'reg_prog_exp'] as $key)
                    @php $def = $formFilters[$key]; @endphp
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label fs-12">{{ $def['label'] }}</label>
                        <select name="{{ $key }}" class="form-select form-select-sm js-mr-filter">
                            <option value="">الكل</option>
                            @foreach($def['options'] as $val => $label)
                                <option value="{{ $val }}" @selected(request($key) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                @foreach(['reg_education_level', 'reg_education_major', 'reg_current_job'] as $key)
                    @php $def = $formFilters[$key]; @endphp
                    <div class="col-md-4 col-xl-3">
                        <label class="form-label fs-12">{{ $def['label'] }}</label>
                        <input type="text" name="{{ $key }}" class="form-control form-control-sm js-mr-filter js-mr-filter-text"
                               placeholder="{{ $def['placeholder'] ?? '' }}"
                               value="{{ request($key) }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</form>
<small id="membershipRequestsFeedback" class="text-muted d-block mt-2"></small>
