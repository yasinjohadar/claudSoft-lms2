@extends('frontend2.layouts.master')

@section('title', ($settings->page_title ?? 'تواصل معنا') . ' | أكاديمية كلاودسوفت')
@section('meta_description', $settings->page_subtitle ?? 'تواصل مع أكاديمية كلاودسوفت — للاستفسارات أو التسجيل في الدورات أو طلب استشارة تقنية.')

@section('content')

    <!-- ============ PAGE BANNER (تواصل معنا) ============ -->
    <section class="page-banner page-banner-contact">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-paper-plane"></i></div>
                <h1 class="page-banner-title">{{ $settings->page_title ?? 'تواصل' }} <span>معنا</span></h1>
                <p class="page-banner-desc">{{ $settings->page_subtitle ?? 'نحن هنا لمساعدتك — للاستفسارات أو التسجيل في الدورات أو طلب استشارة تقنية' }}</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>تواصل معنا</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <!-- ============ CONTACT SECTION ============ -->
    <section class="section-padding contact-page-section">
        <div class="container">
            <div class="row g-4">
                <!-- Contact Form -->
                <div class="col-lg-7">
                    @if($settings->form_enabled ?? true)
                    <div class="glass-panel contact-form-wrapper animate-on-scroll">
                        <h4 style="font-weight:800; margin-bottom:8px;">{{ $settings->form_title ?? 'أرسل لنا رسالة' }}</h4>
                        <p style="color:var(--clr-text-secondary); margin-bottom:25px; font-size:0.95rem;">
                            {{ $settings->form_subtitle ?? 'املأ النموذج أدناه وسنرد عليك في أقرب وقت ممكن' }}
                        </p>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>حدث خطأ!</strong> يرجى التحقق من البيانات المدخلة.
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <form action="{{ route('frontend.contact.send') }}" method="POST" id="contactForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:600; font-size:0.9rem;">الاسم الكامل</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="أدخل اسمك الكامل" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:600; font-size:0.9rem;">البريد الإلكتروني</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="example@email.com" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:600; font-size:0.9rem;">رقم الهاتف</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+963 XXX XXX XXX" value="{{ old('phone') }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:600; font-size:0.9rem;">الموضوع</label>
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="موضوع الرسالة" value="{{ old('subject') }}" required>
                                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-weight:600; font-size:0.9rem;">الرسالة</label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="5" placeholder="اكتب رسالتك هنا..." required>{{ old('message') }}</textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn-primary-custom w-100" style="justify-content:center;">
                                        <i class="fas fa-paper-plane"></i> إرسال الرسالة
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- Contact Info (ديناميكي من لوحة التحكم) -->
                <div class="col-lg-5">
                    <div class="glass-panel contact-info-card animate-on-scroll" style="margin-bottom:20px;">
                        <h4 style="font-weight:800; margin-bottom:20px;">معلومات التواصل</h4>

                        @if(!empty($settings->email_addresses))
                        @foreach($settings->email_addresses as $item)
                            @if(!empty($item['email']))
                            <div class="contact-info-item">
                                <div class="info-icon"><i class="fas {{ $settings->email_icon ?? 'fa-envelope' }}"></i></div>
                                <div>
                                    <h6>{{ $settings->email_title ?? 'البريد الإلكتروني' }}</h6>
                                    <p><a href="mailto:{{ $item['email'] }}">{{ $item['email'] }}</a></p>
                                </div>
                            </div>
                            @break
                            @endif
                        @endforeach
                        @endif

                        @if(!empty($settings->phone_numbers))
                        @foreach($settings->phone_numbers as $item)
                            @if(!empty($item['number']))
                            <div class="contact-info-item">
                                <div class="info-icon"><i class="fas {{ $settings->phone_icon ?? 'fa-phone-alt' }}"></i></div>
                                <div>
                                    <h6>{{ $settings->phone_title ?? 'رقم الهاتف' }}</h6>
                                    <p style="direction:ltr; text-align:right;"><a href="tel:{{ $item['number'] }}">{{ $item['number'] }}</a></p>
                                </div>
                            </div>
                            @break
                            @endif
                        @endforeach
                        @endif

                        @if(!empty($settings->address_text))
                        <div class="contact-info-item">
                            <div class="info-icon"><i class="fas {{ $settings->address_icon ?? 'fa-map-marker-alt' }}"></i></div>
                            <div>
                                <h6>{{ $settings->address_title ?? 'الموقع' }}</h6>
                                <p>{!! $settings->address_text !!}</p>
                            </div>
                        </div>
                        @endif

                        @if(($settings->show_working_hours ?? true) && !empty($settings->working_hours))
                        <div class="contact-info-item" style="margin-bottom:0;">
                            <div class="info-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <h6>{{ $settings->working_hours_title ?? 'ساعات العمل' }}</h6>
                                @foreach($settings->working_hours as $hour)
                                    @if(!empty($hour['day']) && !empty($hour['time']))
                                    <p>{{ $hour['day'] }}: {{ $hour['time'] }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- وسائل التواصل الاجتماعي (ديناميكية) -->
                    @if(!empty($settings->social_links))
                    <div class="glass-panel animate-on-scroll">
                        <h4 style="font-weight:800; margin-bottom:15px;">{{ $settings->social_title ?? 'تابعنا' }}</h4>
                        @if($settings->social_subtitle ?? null)
                        <p style="color:var(--clr-text-secondary); font-size:0.9rem; margin-bottom:15px;">{{ $settings->social_subtitle }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($settings->social_links as $social)
                                @if(($social['enabled'] ?? true) && !empty($social['url']))
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm" title="{{ $social['label'] ?? '' }}">
                                    <i class="fab {{ $social['icon'] ?? 'fa-link' }}"></i> {{ $social['label'] ?? '' }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($settings->show_map && !empty($settings->map_embed_url))
            <div class="mt-5 animate-on-scroll">
                <div class="glass-panel p-0 overflow-hidden" style="border-radius: var(--radius-md);">
                    <iframe src="{{ $settings->map_embed_url }}" width="100%" height="350" style="border:0; display:block;" allowfullscreen="" loading="lazy" title="موقعنا على الخريطة"></iframe>
                </div>
            </div>
            @endif
        </div>
    </section>
@endsection
