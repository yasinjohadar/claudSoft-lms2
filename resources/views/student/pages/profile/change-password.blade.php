@extends('student.layouts.master')

@push('styles')
<style>
    .password-suggestion {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .5rem .75rem;
        border: 1px solid var(--default-border, #e9edf6);
        border-radius: .5rem;
        background: var(--custom-white, #fff);
    }
    .password-suggestion + .password-suggestion { margin-top: .5rem; }
    .password-suggestion__value {
        font-family: "Courier New", Courier, monospace;
        font-size: .95rem;
        letter-spacing: .5px;
        direction: ltr;
        unicode-bidi: bidi-override;
        word-break: break-all;
    }
    .password-suggestion__actions { display: flex; gap: .35rem; flex-shrink: 0; }
    .student-password-input { direction: ltr; text-align: left; }
</style>
@endpush

@section('page-title')
    تغيير كلمة المرور
@stop

@section('content')
<div class="main-content app-content student-profile-page student-profile-password-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">تغيير كلمة المرور</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if($profileLocked ?? false)
                            <li class="breadcrumb-item"><a href="{{ route('student.profile.edit') }}">إكمال الملف الشخصي</a></li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.profile.index') }}">ملفي الشخصي</a></li>
                        @endif
                        <li class="breadcrumb-item active">تغيير كلمة المرور</li>
                    </ol>
                </nav>
                <p class="mb-0 mt-2 fs-13 text-muted">
                    هذه الصفحة مخصّصة لتغيير كلمة المرور فقط، ولا علاقة لها بتعديل بيانات ملفك الشخصي.
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ ($profileLocked ?? false) ? route('student.profile.edit') : route('student.profile.index') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>{{ ($profileLocked ?? false) ? 'العودة لإكمال الملف' : 'العودة للملف' }}
                </a>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-xl-7 col-lg-9">
                <div class="card custom-card student-quizzes-panel">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="avatar avatar-sm bg-warning-transparent">
                                <i class="fe fe-shield text-warning"></i>
                            </span>
                            <div>
                                <h6 class="card-title mb-0">الأمان</h6>
                                <p class="text-muted fs-12 mb-0">اختر كلمة مرور جديدة — لا حاجة لكلمة المرور الحالية</p>
                            </div>
                        </div>

                        <form action="{{ route('student.profile.change-password') }}" method="POST" id="studentChangePasswordForm">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="generateSuggestionsBtn">
                                            <i class="fe fe-refresh-cw me-1"></i>توليد اقتراحات لكلمة المرور
                                        </button>
                                        <span class="badge bg-secondary" id="passwordStrengthBadge">قوة كلمة المرور: —</span>
                                    </div>
                                    <div id="passwordSuggestions" class="mt-3 d-none">
                                        <div class="student-profile-form-hint mb-2">
                                            اختر اقتراحاً واضغط «استخدام» لتعبئته في الحقول، أو «نسخ» لحفظه لديك أولاً.
                                        </div>
                                        <div id="passwordSuggestionsList"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="student-profile-form-label" for="new_password">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" id="new_password" class="form-control student-password-input @error('new_password') is-invalid @enderror" name="new_password" required autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary js-copy-password" data-target="new_password" title="نسخ كلمة المرور">
                                            <i class="fe fe-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="new_password" title="إظهار/إخفاء">
                                            <i class="fe fe-eye"></i>
                                        </button>
                                    </div>
                                    <div class="student-profile-form-hint">8 أحرف على الأقل، حروف كبيرة وصغيرة، أرقام ورموز</div>
                                    @error('new_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="student-profile-form-label" for="new_password_confirmation">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" id="new_password_confirmation" class="form-control student-password-input" name="new_password_confirmation" required autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="new_password_confirmation" title="إظهار/إخفاء">
                                            <i class="fe fe-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="student-profile-field mt-1">
                                        <span class="student-profile-field__icon">
                                            <i class="fe fe-send"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="send_credentials" name="send_credentials" value="1"
                                                       {{ old('send_credentials') ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="send_credentials">
                                                    أرسل لي بيانات الدخول الجديدة
                                                </label>
                                            </div>
                                            <div class="student-profile-form-hint mt-1 mb-0">
                                                ترسل رسالة تحتوي على اسمك وبريدك وكلمة المرور الجديدة، ثم رسالة مستقلة بكلمة المرور وحدها لتسهيل نسخها.
                                            </div>
                                            <div class="student-profile-form-hint mt-1 mb-0">
                                                القنوات:
                                                <span class="fw-semibold" dir="ltr">{{ $student->email }}</span>
                                                @if($whatsappAvailable ?? false)
                                                    + واتساب
                                                    <span class="fw-semibold" dir="ltr">{{ $student->full_phone ?? trim(($student->country_code ?? '') . ($student->phone ?? '')) }}</span>
                                                @else
                                                    <span class="text-muted">(الواتساب غير متاح لحسابك حالياً)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="student-profile-edit-actions">
                                <button type="submit" class="btn btn-warning rounded-pill px-4">
                                    <i class="fe fe-key me-1"></i>تغيير كلمة المرور
                                </button>
                                <a href="{{ ($profileLocked ?? false) ? route('student.profile.edit') : route('student.profile.index') }}" class="btn btn-outline-secondary rounded-pill px-4">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card custom-card student-quizzes-panel mt-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-info-transparent">
                                <i class="fe fe-info text-info"></i>
                            </span>
                            <h6 class="card-title mb-0">نصائح للأمان</h6>
                        </div>
                        <ul class="list-unstyled mb-0 ps-0">
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>لا تشارك كلمة المرور مع أي شخص.</li>
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>استخدم كلمة مرور مختلفة عن باقي حساباتك.</li>
                            <li><i class="fe fe-chevron-left text-primary me-1"></i>تغيير كلمة المرور اختياري — لست مضطراً لتغييرها عند تعديل بياناتك.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    // Same alphabet the platform uses for delivered credentials: no bidi-confusing
    // characters, so the password stays readable when sent over WhatsApp.
    const LOWER = 'abcdefghijkmnopqrstuvwxyz';
    const UPPER = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const DIGITS = '23456789';
    const SYMBOLS = '!@#*';
    const CHARSET = LOWER + UPPER + DIGITS + SYMBOLS;
    const SUGGESTION_COUNT = 3;
    const SUGGESTION_LENGTH = 16;

    function randomInt(max) {
        const buf = new Uint32Array(1);
        window.crypto.getRandomValues(buf);
        return buf[0] % max;
    }

    function pick(source) {
        return source[randomInt(source.length)];
    }

    function generatePassword(length) {
        // Guarantee one of each class so the generated value always passes validation.
        const chars = [pick(LOWER), pick(UPPER), pick(DIGITS), pick(SYMBOLS)];
        for (let i = chars.length; i < length; i++) {
            chars.push(pick(CHARSET));
        }
        for (let i = chars.length - 1; i > 0; i--) {
            const j = randomInt(i + 1);
            [chars[i], chars[j]] = [chars[j], chars[i]];
        }
        return chars.join('');
    }

    function strengthOf(password) {
        if (!password) {
            return { label: '—', css: 'bg-secondary' };
        }
        let score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (password.length >= 16) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (score <= 2) return { label: 'ضعيفة', css: 'bg-danger' };
        if (score <= 4) return { label: 'متوسطة', css: 'bg-warning text-dark' };
        return { label: 'قوية', css: 'bg-success' };
    }

    function updateStrengthBadge(password) {
        const badge = document.getElementById('passwordStrengthBadge');
        if (!badge) return;
        const strength = strengthOf(password);
        badge.className = 'badge ' + strength.css;
        badge.textContent = 'قوة كلمة المرور: ' + strength.label;
    }

    function flashButton(btn, text) {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fe fe-check me-1"></i>' + text;
        btn.disabled = true;
        setTimeout(function () {
            btn.innerHTML = original;
            btn.disabled = false;
        }, 1400);
    }

    function copyText(text, btn, label) {
        const done = function () { flashButton(btn, label); };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(function () {
                legacyCopy(text, done);
            });
        } else {
            legacyCopy(text, done);
        }
    }

    function legacyCopy(text, done) {
        const helper = document.createElement('textarea');
        helper.value = text;
        helper.setAttribute('readonly', '');
        helper.style.position = 'fixed';
        helper.style.opacity = '0';
        document.body.appendChild(helper);
        helper.select();
        try {
            document.execCommand('copy');
            done();
        } catch (e) {
            window.prompt('انسخ كلمة المرور يدوياً:', text);
        }
        document.body.removeChild(helper);
    }

    function applyPassword(value) {
        const newInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('new_password_confirmation');
        if (!newInput || !confirmInput) return;

        newInput.value = value;
        confirmInput.value = value;
        newInput.type = 'text';
        confirmInput.type = 'text';
        updateStrengthBadge(value);
        newInput.focus();
    }

    function renderSuggestions() {
        const wrapper = document.getElementById('passwordSuggestions');
        const list = document.getElementById('passwordSuggestionsList');
        if (!wrapper || !list) return;

        list.innerHTML = '';

        for (let i = 0; i < SUGGESTION_COUNT; i++) {
            const value = generatePassword(SUGGESTION_LENGTH);

            const row = document.createElement('div');
            row.className = 'password-suggestion';

            const valueEl = document.createElement('span');
            valueEl.className = 'password-suggestion__value';
            valueEl.textContent = value;

            const actions = document.createElement('div');
            actions.className = 'password-suggestion__actions';

            const useBtn = document.createElement('button');
            useBtn.type = 'button';
            useBtn.className = 'btn btn-sm btn-primary';
            useBtn.innerHTML = '<i class="fe fe-check me-1"></i>استخدام';
            useBtn.addEventListener('click', function () {
                applyPassword(value);
                flashButton(useBtn, 'تم');
            });

            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'btn btn-sm btn-outline-secondary';
            copyBtn.innerHTML = '<i class="fe fe-copy me-1"></i>نسخ';
            copyBtn.addEventListener('click', function () {
                copyText(value, copyBtn, 'تم النسخ');
            });

            actions.appendChild(useBtn);
            actions.appendChild(copyBtn);
            row.appendChild(valueEl);
            row.appendChild(actions);
            list.appendChild(row);
        }

        wrapper.classList.remove('d-none');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('studentChangePasswordForm');
        if (!form) return;

        const generateBtn = document.getElementById('generateSuggestionsBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', renderSuggestions);
        }

        const newInput = document.getElementById('new_password');
        if (newInput) {
            newInput.addEventListener('input', function () {
                updateStrengthBadge(newInput.value);
            });
        }

        form.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.js-toggle-password');
            if (toggleBtn) {
                const input = document.getElementById(toggleBtn.dataset.target);
                if (!input) return;
                const icon = toggleBtn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) icon.className = 'fe fe-eye-off';
                } else {
                    input.type = 'password';
                    if (icon) icon.className = 'fe fe-eye';
                }
                return;
            }

            const copyBtn = e.target.closest('.js-copy-password');
            if (copyBtn) {
                const input = document.getElementById(copyBtn.dataset.target);
                if (!input || !input.value) return;
                copyText(input.value, copyBtn, '');
            }
        });
    });
})();
</script>
@endpush
