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
                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
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
                                        name="country_code" id="country_code_select">
                                    <option value="+966" selected>🇸🇦 السعودية (+966)</option>
                                    <option value="+971">🇦🇪 الإمارات (+971)</option>
                                    <option value="+20">🇪🇬 مصر (+20)</option>
                                    <option value="+962">🇯🇴 الأردن (+962)</option>
                                    <option value="+965">🇰🇼 الكويت (+965)</option>
                                    <option value="+973">🇧🇭 البحرين (+973)</option>
                                    <option value="+974">🇶🇦 قطر (+974)</option>
                                    <option value="+968">🇴🇲 عمان (+968)</option>
                                    <option value="+961">🇱🇧 لبنان (+961)</option>
                                    <option value="+963">🇸🇾 سوريا (+963)</option>
                                    <option value="+964">🇮🇶 العراق (+964)</option>
                                    <option value="+967">🇾🇪 اليمن (+967)</option>
                                    <option value="+212">🇲🇦 المغرب (+212)</option>
                                    <option value="+213">🇩🇿 الجزائر (+213)</option>
                                    <option value="+216">🇹🇳 تونس (+216)</option>
                                    <option value="+218">🇱🇾 ليبيا (+218)</option>
                                    <option value="+249">🇸🇩 السودان (+249)</option>
                                    <option value="+90">🇹🇷 تركيا (+90)</option>
                                    <option value="+1">🇺🇸 أمريكا (+1)</option>
                                    <option value="+44">🇬🇧 بريطانيا (+44)</option>
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
                                <div class="form-floating">
                                    <select class="form-select @error('nationality_id') is-invalid @enderror"
                                            name="nationality_id" aria-label="الجنسية">
                                        <option value="">اختر الجنسية</option>
                                        @foreach ($nationalities as $nationality)
                                            <option value="{{ $nationality->id }}"
                                                    {{ old('nationality_id') == $nationality->id ? 'selected' : '' }}>
                                                {{ $nationality->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label>الجنسية</label>
                                    @error('nationality_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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

        // تحديث الرقم الكامل
        function updateFullPhone() {
            const countryCode = document.getElementById('country_code_select').value;
            const phone = document.getElementById('phone_input').value;

            if (phone) {
                const fullPhone = countryCode + phone.replace(/\D/g, ''); // إزالة أي رموز غير رقمية
                document.getElementById('full_phone').value = fullPhone;
                console.log('Full Phone:', fullPhone);
            }
        }

        // عند تغيير رمز الدولة أو رقم الهاتف
        document.getElementById('country_code_select').addEventListener('change', updateFullPhone);
        document.getElementById('phone_input').addEventListener('input', updateFullPhone);

        // قبل إرسال النموذج
        document.querySelector('form').addEventListener('submit', function(e) {
            updateFullPhone();
        });

        // تفعيل Select2 للأدوار وللدول
        $(document).ready(function() {
            $('select[name="roles[]"]').select2({
                placeholder: "اختر الأدوار",
                allowClear: true,
                dir: "rtl",
                theme: "bootstrap-5"
            });

            $('.country-code-select').select2({
                placeholder: "اختر رمز الدولة",
                allowClear: false,
                dir: "ltr",
                width: '100%',
                theme: "bootstrap-5"
            });
        });
    </script>
@stop
