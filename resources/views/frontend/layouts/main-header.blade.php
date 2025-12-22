
<style>
/* Force remove all borders and outlines from navbar elements */
.buttom-header .nav-item,
.buttom-header .nav-link,
.buttom-header .navbar-nav .nav-item,
.buttom-header .navbar-nav .nav-link,
header .nav-item,
header .nav-link,
header .navbar-nav .nav-item,
header .navbar-nav .nav-link {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}
.buttom-header .nav-item:focus,
.buttom-header .nav-link:focus,
.buttom-header .nav-item:focus-visible,
.buttom-header .nav-link:focus-visible,
.buttom-header .nav-item:focus-within,
.buttom-header .nav-link:focus-within,
.buttom-header .nav-item.active,
.buttom-header .nav-link.active,
.buttom-header .nav-item:hover,
.buttom-header .nav-link:hover {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}
</style>

  <!-- Start Header -->
<header>
    <div class="top-header d-flex align-items-center py-2">
        <div class="container">
            <div class="row">

                <div class="col-6 col-md-6 col-lg-4 d-flex align-items-center">
                    <a href="/" class="me-3">
                        <img width="60" src="{{ asset('frontend/assets/images/logo.png') }}" alt="logo">
                    </a>
                    <div class="header-contact-info d-none d-lg-flex align-items-center gap-4">
                        @php
                            $contactSettings = $contactSettings ?? \App\Models\ContactSetting::getSettings();
                            $email = $contactSettings->email_addresses[0]['email'] ?? '';
                            $phone = $contactSettings->phone_numbers[0]['number'] ?? '';
                        @endphp
                        @if($email)
                        <div class="contact-item d-flex align-items-center">
                            <i class="fa-solid fa-envelope contact-icon ms-2"></i>
                            <span class="contact-label ms-2">للمراسلة</span>
                            <span class="contact-text">{{ $email }}</span>
                        </div>
                        @endif
                        @if($phone)
                        <div class="contact-item d-flex align-items-center">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="contact-icon-link ms-2">
                                <i class="fa-brands fa-whatsapp contact-icon"></i>
                            </a>
                            <span class="contact-label ms-2">للتواصل</span>
                            <span class="contact-text">{{ $phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>



                <div class="col-6 col-md-6 col-lg-4 d-flex align-items-center">
                    <input type="text" class="form-control search-input" placeholder="اكتب   للبحث ">
                </div>




                <div class="col-md-4 col-lg-4 left-header d-flex align-items-center d-none d-lg-flex justify-content-end">
                    <a href="/blog" role="button" >
                        <span>المدونة</span>
                        <i class="fa-solid fa-book"></i>
                    </a>
                    @auth
                        @php
                            $user = auth()->user();
                            $dashboardRoute = $user->hasRole('admin') ? route('admin.dashboard') : ($user->hasRole('student') ? route('student.dashboard') : route('dashboard'));
                        @endphp
                        <a href="{{ $dashboardRoute }}" role="button">
                            <span>لوحة التحكم</span>
                            <i class="fa-solid fa-tachometer-alt"></i>
                        </a>
                    @else
                        <a href="/login" role="button">
                            <span>تسجيل الدخول</span>
                            <i class="fa-solid fa-user"></i>
                        </a>
                    @endauth
                    <a href="/courses">
                        <span>الكورسات</span>
                        <i class="fa-solid fa-computer"></i>
                    </a>
                </div>



            </div>
        </div>
    </div>

    <div class="buttom-header">
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container">

              <!-- <a class="navbar-brand" href="#">Navbar</a> -->
              <button class="navbar-toggler menu-icon" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>


              <div>
                <button class="navbar-toggler menu-icon" type="button" data-bs-toggle="offcanvas" href="#cart" >
                    <i class="fa-solid fa-cart-shopping"></i>
                  </button>
                @auth
                    @php
                        $user = auth()->user();
                        $dashboardRoute = $user->hasRole('admin') ? route('admin.dashboard') : ($user->hasRole('student') ? route('student.dashboard') : route('dashboard'));
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="navbar-toggler menu-icon" style="border:none!important;outline:none!important;box-shadow:none!important;background:transparent!important;">
                        <i class="fa-solid fa-tachometer-alt"></i>
                    </a>
                @else
                    <button class="navbar-toggler menu-icon" type="button" data-bs-toggle="offcanvas" href="#login" >
                        <i class="fa-solid fa-user"></i>
                    </button>
                @endauth
              </div>


              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link active" style="border:none!important;outline:none!important;box-shadow:none!important;" aria-current="page" href="{{ route('frontend.home') }}">الرئيسية</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="{{ route('frontend.courses.index') }}">الكورسات</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="{{ route('frontend.reviews.index') }}">آراء الطلاب</a>
                  </li>
         
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">من نحن</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="{{ route('frontend.blog.index') }}">المدونة</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="{{ route('frontend.contact') }}">اتصل بنا</a>
                  </li>


                </ul>

              </div>
            </div>
          </nav>
    </div>
</header>
  <!-- End Header -->
