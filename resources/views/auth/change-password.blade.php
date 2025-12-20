<!DOCTYPE html>
<html lang="en" dir="rtl" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>تغيير كلمة المرور - أكاديمية كلاودسوفت التقنية</title>
    <meta name="Description" content="تغيير كلمة المرور">

    <!-- Favicon -->
    <link rel="icon" href="../assets/images/brand-logos/favicon.ico" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="../assets/js/authentication-main.js"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="../assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style Css -->
    <link href="../assets/css/styles.min.css" rel="stylesheet">

    <!-- Icons Css -->
    <link href="../assets/css/icons.min.css" rel="stylesheet">

    <style>
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                <div class="my-5 d-flex justify-content-center">
                    <a href="/">
                        <img src="../assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                        <img src="../assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                    </a>
                </div>

                <div class="card custom-card shadow">
                    <div class="card-body p-5">
                        <p class="h5 fw-semibold mb-2 text-center">تغيير كلمة المرور</p>
                        <p class="mb-4 text-muted op-7 fw-normal text-center">يرجى تغيير كلمة المرور للمتابعة</p>

                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.change.update') }}">
                            @csrf

                            <div class="row gy-3">
                                <!-- Current Password -->
                                <div class="col-xl-12">
                                    <label for="current_password" class="form-label text-default">كلمة المرور الحالية</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg @error('current_password') is-invalid @enderror"
                                               id="current_password"
                                               name="current_password"
                                               placeholder="أدخل كلمة المرور الحالية"
                                               required>
                                        <button class="btn btn-light" type="button" onclick="togglePassword('current_password')">
                                            <i class="fa fa-eye" id="current_password-icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">كلمة المرور الافتراضية: <strong>pass@claud123</strong></small>
                                </div>

                                <!-- New Password -->
                                <div class="col-xl-12">
                                    <label for="new_password" class="form-label text-default">كلمة المرور الجديدة</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg @error('new_password') is-invalid @enderror"
                                               id="new_password"
                                               name="new_password"
                                               placeholder="أدخل كلمة المرور الجديدة"
                                               required>
                                        <button class="btn btn-light" type="button" onclick="togglePassword('new_password')">
                                            <i class="fa fa-eye" id="new_password-icon"></i>
                                        </button>
                                    </div>
                                    <div class="password-requirements">
                                        <i class="fa fa-info-circle me-1"></i>
                                        يجب أن تكون كلمة المرور على الأقل 8 أحرف
                                    </div>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="col-xl-12 mb-2">
                                    <label for="new_password_confirmation" class="form-label text-default">تأكيد كلمة المرور الجديدة</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control form-control-lg"
                                               id="new_password_confirmation"
                                               name="new_password_confirmation"
                                               placeholder="أعد إدخال كلمة المرور الجديدة"
                                               required>
                                        <button class="btn btn-light" type="button" onclick="togglePassword('new_password_confirmation')">
                                            <i class="fa fa-eye" id="new_password_confirmation-icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-xl-12 d-grid mt-4">
                                    <button type="submit" class="btn btn-lg btn-primary">
                                        <i class="fa fa-check me-2"></i>
                                        تغيير كلمة المرور
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted">
                                    <i class="fa fa-sign-out-alt me-1"></i>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>
