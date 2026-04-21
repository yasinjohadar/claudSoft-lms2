@extends('admin.layouts.master')

@section('page-title')
    إنشاء مستخدم جديد
@stop

@section('css')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        .form-floating label {
            right: auto;
            left: 0.75rem;
        }

        select.form-select {
            padding: 0.75rem;
        }

        /* أعلام الدول مستطيلة وليست دائرية */
        .select2-container--bootstrap-5 .select2-results__option img,
        .select2-container--bootstrap-5 .select2-selection__rendered img {
            border-radius: 0 !important;
        }

        /* قائمة رمز الدولة: وضوح رمز ISO */
        .country-code-select,
        .country-code-select option,
        .select2-container--bootstrap-5 .country-code-select + .select2 .select2-selection__rendered {
            font-size: 1.05rem;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }

        .photo-upload {
            position: relative;
            display: inline-block;
        }

        .photo-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .photo-upload-label {
            cursor: pointer;
            display: inline-block;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #6c757d;
            transition: all 0.3s;
        }

        .photo-upload-label:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Phone Input Styles */
        .country-code-select {
            direction: ltr;
            text-align: left;
        }

        #phone_input {
            direction: ltr;
            text-align: left;
        }

        /* Select2 RTL Support */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }
    </style>
@stop

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">إنشاء مستخدم جديد</h5>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" data-phone-ajax-validate>
                        @csrf

                        <div class="row g-3">
                            <!-- المعلومات الأساسية -->
                            <div class="col-12">
                                <h6 class="text-primary mb-3">المعلومات الأساسية</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           name="name" placeholder="الاسم الكامل" value="{{ old('name') }}" required>
                                    <label>الاسم الكامل (بالإنجليزية) <span class="text-danger">*</span></label>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                                           name="name_ar" placeholder="الاسم بالعربي" value="{{ old('name_ar') }}" dir="rtl">
                                    <label>الاسم بالعربي</label>
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           name="email" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
                                    <label>البريد الإلكتروني <span class="text-danger">*</span></label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">رمز الدولة</label>
                                <select class="form-select country-code-select @error('country_code') is-invalid @enderror"
                                        name="country_code" id="country_code_select" data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                    <option value="">اختر رمز الدولة</option>
                                    @foreach(config('country_codes.list', []) as $code => $label)
                                        @php
                                            $isoList = config('country_codes.iso', []);
                                            $iso = $isoList[$code] ?? '';
                                            $textOnly = config('country_codes.list_text_only', [])[$code] ?? $label;
                                            $separator = config('country_codes.separator', '  ·  ');
                                            $display = $iso !== '' ? $textOnly . $separator . $iso : $textOnly;
                                        @endphp
                                        <option value="{{ $code }}" data-iso="{{ strtolower($iso) }}" {{ old('country_code', config('country_codes.default')) == $code ? 'selected' : '' }}>
                                            {{ $display }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">رقم الهاتف (WhatsApp)</label>
                                <input type="tel"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       name="phone"
                                       id="phone_input"
                                       placeholder="512345678"
                                       value="{{ old('phone') }}">

                                <!-- حقل مخفي للرقم الكامل -->
                                <input type="hidden" name="full_phone" id="full_phone">

                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('full_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">للواتساب</small>
                                <div class="small mt-1 phone-country-ajax-feedback" data-phone-ajax-feedback aria-live="polite"></div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                           name="national_id" placeholder="رقم الهوية" value="{{ old('national_id') }}">
                                    <label>رقم الهوية</label>
                                    @error('national_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">الجنسية</label>
                                <div class="d-flex align-items-center gap-2">
                                    <img id="admin-nationality-flag-preview" src="" alt="" style="width:20px;height:15px;object-fit:cover;border-radius:0;flex-shrink:0;display:none;">
                                    <select class="form-select @error('nationality_id') is-invalid @enderror"
                                            name="nationality_id" id="admin_nationality_id_select" aria-label="الجنسية" data-flag-url="{{ config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png') }}">
                                        <option value="">اختر الجنسية</option>
                                        @foreach ($nationalities as $nationality)
                                            @php
                                                $isoMap = config('country_codes.nationality_iso', []);
                                                $displayMap = config('country_codes.nationality_display', []);
                                                $iso = $isoMap[$nationality->name] ?? '';
                                                $displayText = $displayMap[$nationality->name] ?? $nationality->name;
                                            @endphp
                                            <option value="{{ $nationality->id }}" data-flag-iso="{{ $iso }}"
                                                    {{ old('nationality_id') == $nationality->id ? 'selected' : '' }}>
                                                {{ $displayText }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('nationality_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- كلمة المرور -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" placeholder="كلمة المرور" required>
                                    <label>كلمة المرور <span class="text-danger">*</span></label>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           name="password_confirmation" placeholder="تأكيد كلمة المرور" required>
                                    <label>تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- صورة المستخدم -->
                            <div class="col-md-6">
                                <label class="form-label">صورة المستخدم</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="photo-upload">
                                        <img id="photo-preview" src="{{ asset('assets/images/faces/default-avatar.jpg') }}" 
                                             alt="صورة المستخدم" class="photo-preview">
                                        <input type="file" name="photo" id="photo-input" accept="image/*" 
                                               onchange="previewPhoto(this)">
                                    </div>
                                    <div>
                                        <label for="photo-input" class="photo-upload-label">
                                            <i class="fas fa-camera me-2"></i>اختر صورة
                                        </label>
                                    </div>
                                </div>
                                @error('photo')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- حالة المستخدم -->
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('status') is-invalid @enderror" name="status" aria-label="حالة المستخدم">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                        <option value="banned" {{ old('status') == 'banned' ? 'selected' : '' }}>محظور</option>
                                    </select>
                                    <label>حالة المستخدم</label>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- تفعيل الحساب -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                           id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        تفعيل الحساب
                                    </label>
                                </div>
                            </div>

                            <!-- الأدوار -->
                            <div class="col-12">
                                <label class="form-label mt-3">الأدوار (Roles)</label>
                                <select class="form-select @error('roles') is-invalid @enderror" name="roles[]" multiple>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" 
                                                {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">اضغط Ctrl (أو Cmd على Mac) لاختيار أكثر من دور</div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary px-4 me-2">
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>حفظ بيانات المستخدم
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // تفعيل Select2 للأدوار وللدول
        $(document).ready(function() {
            $('select[name="roles[]"]').select2({
                placeholder: "اختر الأدوار",
                allowClear: true,
                dir: "rtl",
                theme: "bootstrap-5"
            });

            var flagUrlTemplate = $('#country_code_select').attr('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';
            $('.country-code-select').select2({
                placeholder: "اختر رمز الدولة",
                allowClear: false,
                dir: "ltr",
                width: '100%',
                theme: "bootstrap-5",
                templateResult: function(state) {
                    if (!state.id) return state.text;
                    var iso = $(state.element).data('iso') || 'sa';
                    var url = flagUrlTemplate.replace('{iso}', iso);
                    var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                    $span.append($('<img src="' + url + '" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
                    $span.append(document.createTextNode(state.text));
                    return $span;
                },
                templateSelection: function(state) {
                    if (!state.id) return state.text;
                    var iso = $(state.element).data('iso') || 'sa';
                    var url = flagUrlTemplate.replace('{iso}', iso);
                    var $span = $('<span class="d-flex align-items-center gap-2"></span>');
                    $span.append($('<img src="' + url + '" style="width:20px;height:15px;object-fit:cover;border-radius:0;">'));
                    $span.append(document.createTextNode(state.text));
                    return $span;
                }
            });

            function updateAdminNationalityFlag() {
                var sel = document.getElementById('admin_nationality_id_select');
                var img = document.getElementById('admin-nationality-flag-preview');
                if (!sel || !img) return;
                var opt = sel.options[sel.selectedIndex];
                var iso = opt && opt.getAttribute('data-flag-iso') ? opt.getAttribute('data-flag-iso') : '';
                var urlTemplate = sel.getAttribute('data-flag-url') || 'https://flagcdn.com/w20/{iso}.png';
                if (iso) {
                    img.src = urlTemplate.replace('{iso}', iso.toLowerCase());
                    img.style.display = 'block';
                } else {
                    img.style.display = 'none';
                }
            }
            if (document.getElementById('admin_nationality_id_select')) {
                updateAdminNationalityFlag();
                $('#admin_nationality_id_select').on('change', updateAdminNationalityFlag);
            }
        });
    </script>
    @include('components.phone-country-ajax-script')
@stop
