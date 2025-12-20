@extends('admin.layouts.master')

@section('page-title')
    إعدادات صفحة الاتصال
@stop

@section('css')
    <style>
        /* Enhanced Card Design - Consistent with other pages */
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .form-section:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px);
        }

        .form-section:hover::before {
            opacity: 1;
        }

        .form-section-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-section:hover .form-section-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .bg-primary-transparent {
            background: rgba(102, 126, 234, 0.1);
        }

        .bg-danger-transparent {
            background: rgba(220, 53, 69, 0.1);
        }

        .bg-success-transparent {
            background: rgba(25, 135, 84, 0.1);
        }

        .bg-info-transparent {
            background: rgba(13, 202, 240, 0.1);
        }

        .bg-warning-transparent {
            background: rgba(255, 193, 7, 0.1);
        }

        .bg-secondary-transparent {
            background: rgba(108, 117, 125, 0.1);
        }

        .dynamic-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .dynamic-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .btn-remove-item {
            margin-top: 0;
        }

        .required-asterisk {
            color: #dc3545;
            margin-right: 3px;
            font-weight: 700;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.625rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .page-header-breadcrumb {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 2px solid #e9ecef;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.625rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            border-radius: 10px;
            padding: 0.625rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        .btn-sm {
            border-radius: 8px;
            padding: 0.4rem 1rem;
            font-weight: 600;
        }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.1);
            border-radius: 16px 16px 0 0;
            margin: 2rem -2rem -2rem;
            border-top: 3px solid #667eea;
            z-index: 100;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            animation: fadeInUp 0.5s ease-out;
        }

        .form-section:nth-child(1) { animation-delay: 0.1s; }
        .form-section:nth-child(2) { animation-delay: 0.2s; }
        .form-section:nth-child(3) { animation-delay: 0.3s; }
        .form-section:nth-child(4) { animation-delay: 0.4s; }
        .form-section:nth-child(5) { animation-delay: 0.5s; }
        .form-section:nth-child(6) { animation-delay: 0.6s; }
        .form-section:nth-child(7) { animation-delay: 0.7s; }
    </style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إعدادات صفحة الاتصال</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إعدادات صفحة الاتصال</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('admin.contact-settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- معلومات الصفحة -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-primary-transparent text-primary">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            معلومات الصفحة
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان الصفحة
                            </label>
                            <input type="text" name="page_title" class="form-control @error('page_title') is-invalid @enderror" 
                                   value="{{ old('page_title', $settings->page_title) }}" required>
                            @error('page_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وصف الصفحة</label>
                            <input type="text" name="page_subtitle" class="form-control @error('page_subtitle') is-invalid @enderror" 
                                   value="{{ old('page_subtitle', $settings->page_subtitle) }}">
                            @error('page_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- معلومات العنوان -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-danger-transparent text-danger">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            معلومات العنوان
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان البطاقة
                            </label>
                            <input type="text" name="address_title" class="form-control @error('address_title') is-invalid @enderror" 
                                   value="{{ old('address_title', $settings->address_title) }}" required>
                            @error('address_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أيقونة العنوان</label>
                            <input type="text" name="address_icon" class="form-control @error('address_icon') is-invalid @enderror" 
                                   value="{{ old('address_icon', $settings->address_icon) }}" 
                                   placeholder="fa-location-dot">
                            @error('address_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">نص العنوان</label>
                            <textarea name="address_text" class="form-control @error('address_text') is-invalid @enderror" rows="3">{{ old('address_text', $settings->address_text) }}</textarea>
                            <small class="text-muted">يمكنك استخدام &lt;br&gt; للسطر الجديد</small>
                            @error('address_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- معلومات الهاتف -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-success-transparent text-success">
                                <i class="fas fa-phone"></i>
                            </div>
                            معلومات الهاتف
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان البطاقة
                            </label>
                            <input type="text" name="phone_title" class="form-control @error('phone_title') is-invalid @enderror" 
                                   value="{{ old('phone_title', $settings->phone_title) }}" required>
                            @error('phone_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أيقونة الهاتف</label>
                            <input type="text" name="phone_icon" class="form-control @error('phone_icon') is-invalid @enderror" 
                                   value="{{ old('phone_icon', $settings->phone_icon) }}" 
                                   placeholder="fa-phone">
                            @error('phone_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">أرقام الهاتف</label>
                        <div id="phone-numbers-container">
                            @foreach(old('phone_numbers', $settings->phone_numbers ?? []) as $index => $phone)
                                <div class="dynamic-item">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" name="phone_numbers[{{ $index }}][number]" 
                                                   class="form-control" value="{{ $phone['number'] ?? '' }}" 
                                                   placeholder="+966551966588">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="phone_numbers[{{ $index }}][label]" 
                                                   class="form-control" value="{{ $phone['label'] ?? '' }}" 
                                                   placeholder="تسمية (اختياري)">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="add-phone-number">
                            <i class="fas fa-plus me-1"></i>إضافة رقم
                        </button>
                    </div>
                </div>

                <!-- معلومات البريد الإلكتروني -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-info-transparent text-info">
                                <i class="fas fa-envelope"></i>
                            </div>
                            معلومات البريد الإلكتروني
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان البطاقة
                            </label>
                            <input type="text" name="email_title" class="form-control @error('email_title') is-invalid @enderror" 
                                   value="{{ old('email_title', $settings->email_title) }}" required>
                            @error('email_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">أيقونة البريد</label>
                            <input type="text" name="email_icon" class="form-control @error('email_icon') is-invalid @enderror" 
                                   value="{{ old('email_icon', $settings->email_icon) }}" 
                                   placeholder="fa-envelope">
                            @error('email_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">عناوين البريد الإلكتروني</label>
                        <div id="email-addresses-container">
                            @foreach(old('email_addresses', $settings->email_addresses ?? []) as $index => $email)
                                <div class="dynamic-item">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="email" name="email_addresses[{{ $index }}][email]" 
                                                   class="form-control" value="{{ $email['email'] ?? '' }}" 
                                                   placeholder="info@example.com">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="email_addresses[{{ $index }}][label]" 
                                                   class="form-control" value="{{ $email['label'] ?? '' }}" 
                                                   placeholder="تسمية (اختياري)">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="add-email-address">
                            <i class="fas fa-plus me-1"></i>إضافة بريد
                        </button>
                    </div>
                </div>

                <!-- الخريطة -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-warning-transparent text-warning">
                                <i class="fas fa-map"></i>
                            </div>
                            إعدادات الخريطة
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="show_map" value="1" 
                                       id="show_map" {{ old('show_map', $settings->show_map) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_map">عرض الخريطة</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">رابط الخريطة (Embed URL)</label>
                            <textarea name="map_embed_url" class="form-control @error('map_embed_url') is-invalid @enderror" rows="3">{{ old('map_embed_url', $settings->map_embed_url) }}</textarea>
                            <small class="text-muted">انسخ رابط embed من Google Maps</small>
                            @error('map_embed_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- وسائل التواصل الاجتماعي -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-primary-transparent text-primary">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            وسائل التواصل الاجتماعي
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان القسم
                            </label>
                            <input type="text" name="social_title" class="form-control @error('social_title') is-invalid @enderror" 
                                   value="{{ old('social_title', $settings->social_title) }}" required>
                            @error('social_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وصف القسم</label>
                            <input type="text" name="social_subtitle" class="form-control @error('social_subtitle') is-invalid @enderror" 
                                   value="{{ old('social_subtitle', $settings->social_subtitle) }}">
                            @error('social_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">روابط وسائل التواصل</label>
                        <div id="social-links-container">
                            @foreach(old('social_links', $settings->social_links ?? []) as $index => $social)
                                <div class="dynamic-item">
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <input type="text" name="social_links[{{ $index }}][platform]" 
                                                   class="form-control" value="{{ $social['platform'] ?? '' }}" 
                                                   placeholder="facebook">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="url" name="social_links[{{ $index }}][url]" 
                                                   class="form-control" value="{{ $social['url'] ?? '' }}" 
                                                   placeholder="https://...">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="text" name="social_links[{{ $index }}][icon]" 
                                                   class="form-control" value="{{ $social['icon'] ?? '' }}" 
                                                   placeholder="fa-facebook-f">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="social_links[{{ $index }}][label]" 
                                                   class="form-control" value="{{ $social['label'] ?? '' }}" 
                                                   placeholder="فيسبوك">
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="social_links[{{ $index }}][enabled]" value="1" 
                                                       {{ ($social['enabled'] ?? true) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="add-social-link">
                            <i class="fas fa-plus me-1"></i>إضافة رابط
                        </button>
                    </div>
                </div>

                <!-- ساعات العمل -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-secondary-transparent text-secondary">
                                <i class="fas fa-clock"></i>
                            </div>
                            ساعات العمل
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان القسم
                            </label>
                            <input type="text" name="working_hours_title" class="form-control @error('working_hours_title') is-invalid @enderror" 
                                   value="{{ old('working_hours_title', $settings->working_hours_title) }}" required>
                            @error('working_hours_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_working_hours" value="1" 
                                       id="show_working_hours" {{ old('show_working_hours', $settings->show_working_hours) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_working_hours">عرض ساعات العمل</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">ساعات العمل</label>
                        <div id="working-hours-container">
                            @foreach(old('working_hours', $settings->working_hours ?? []) as $index => $hour)
                                <div class="dynamic-item">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <input type="text" name="working_hours[{{ $index }}][day]" 
                                                   class="form-control" value="{{ $hour['day'] ?? '' }}" 
                                                   placeholder="السبت - الخميس">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="working_hours[{{ $index }}][time]" 
                                                   class="form-control" value="{{ $hour['time'] ?? '' }}" 
                                                   placeholder="9:00 ص - 6:00 م">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="add-working-hour">
                            <i class="fas fa-plus me-1"></i>إضافة ساعة عمل
                        </button>
                    </div>
                </div>

                <!-- نموذج الاتصال -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-title">
                            <div class="form-section-icon bg-success-transparent text-success">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            نموذج الاتصال
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <span class="required-asterisk">*</span>
                                عنوان النموذج
                            </label>
                            <input type="text" name="form_title" class="form-control @error('form_title') is-invalid @enderror" 
                                   value="{{ old('form_title', $settings->form_title) }}" required>
                            @error('form_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وصف النموذج</label>
                            <input type="text" name="form_subtitle" class="form-control @error('form_subtitle') is-invalid @enderror" 
                                   value="{{ old('form_subtitle', $settings->form_subtitle) }}">
                            @error('form_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="form_enabled" value="1" 
                                       id="form_enabled" {{ old('form_enabled', $settings->form_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="form_enabled">تفعيل نموذج الاتصال</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-section sticky-actions">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-wave">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary btn-wave">
                            <i class="fas fa-save me-2"></i>حفظ التغييرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('script')
    <script>
        let phoneIndex = {{ count(old('phone_numbers', $settings->phone_numbers ?? [])) }};
        let emailIndex = {{ count(old('email_addresses', $settings->email_addresses ?? [])) }};
        let socialIndex = {{ count(old('social_links', $settings->social_links ?? [])) }};
        let workingHoursIndex = {{ count(old('working_hours', $settings->working_hours ?? [])) }};

        // إضافة رقم هاتف
        document.getElementById('add-phone-number')?.addEventListener('click', function() {
            const container = document.getElementById('phone-numbers-container');
            const item = document.createElement('div');
            item.className = 'dynamic-item';
            item.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" name="phone_numbers[${phoneIndex}][number]" 
                               class="form-control" placeholder="+966551966588">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="phone_numbers[${phoneIndex}][label]" 
                               class="form-control" placeholder="تسمية (اختياري)">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
            phoneIndex++;
            attachRemoveListeners();
        });

        // إضافة بريد إلكتروني
        document.getElementById('add-email-address')?.addEventListener('click', function() {
            const container = document.getElementById('email-addresses-container');
            const item = document.createElement('div');
            item.className = 'dynamic-item';
            item.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="email" name="email_addresses[${emailIndex}][email]" 
                               class="form-control" placeholder="info@example.com">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="email_addresses[${emailIndex}][label]" 
                               class="form-control" placeholder="تسمية (اختياري)">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
            emailIndex++;
            attachRemoveListeners();
        });

        // إضافة رابط اجتماعي
        document.getElementById('add-social-link')?.addEventListener('click', function() {
            const container = document.getElementById('social-links-container');
            const item = document.createElement('div');
            item.className = 'dynamic-item';
            item.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-2">
                        <input type="text" name="social_links[${socialIndex}][platform]" 
                               class="form-control" placeholder="facebook">
                    </div>
                    <div class="col-md-3">
                        <input type="url" name="social_links[${socialIndex}][url]" 
                               class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="social_links[${socialIndex}][icon]" 
                               class="form-control" placeholder="fa-facebook-f">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="social_links[${socialIndex}][label]" 
                               class="form-control" placeholder="فيسبوك">
                    </div>
                    <div class="col-md-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   name="social_links[${socialIndex}][enabled]" value="1" checked>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
            socialIndex++;
            attachRemoveListeners();
        });

        // إضافة ساعة عمل
        document.getElementById('add-working-hour')?.addEventListener('click', function() {
            const container = document.getElementById('working-hours-container');
            const item = document.createElement('div');
            item.className = 'dynamic-item';
            item.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="working_hours[${workingHoursIndex}][day]" 
                               class="form-control" placeholder="السبت - الخميس">
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="working_hours[${workingHoursIndex}][time]" 
                               class="form-control" placeholder="9:00 ص - 6:00 م">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(item);
            workingHoursIndex++;
            attachRemoveListeners();
        });

        // إزالة عنصر
        function attachRemoveListeners() {
            document.querySelectorAll('.btn-remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.dynamic-item').remove();
                });
            });
        }

        // تفعيل عند التحميل
        attachRemoveListeners();
    </script>
@stop

