@extends('student.layouts.master')

@section('page-title')
تعديل الملف الشخصي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong><i class="fa fa-times-circle me-1"></i> خطأ!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h4 class="page-title fs-24 mb-1 fw-bold">
                    <i class="fa fa-edit text-primary me-2"></i>
                    تعديل الملف الشخصي
                </h4>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.profile.index') }}">ملفي الشخصي</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <a href="{{ route('student.profile.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>العودة
                </a>
            </div>
        </div>

        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-user me-2"></i>البيانات الأساسية</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $student->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $student->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم الهاتف</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $student->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم الهوية</label>
                                    <input type="text" class="form-control @error('national_id') is-invalid @enderror" name="national_id" value="{{ old('national_id', $student->national_id) }}">
                                    @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">تاريخ الميلاد</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth) }}">
                                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجنس</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                        <option value="">اختر الجنس</option>
                                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>ذكر</option>
                                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>أنثى</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">المدينة</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $student->city) }}">
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">الجنسية</label>
                                    <select class="form-select @error('nationality_id') is-invalid @enderror" name="nationality_id">
                                        <option value="">اختر الجنسية</option>
                                        @foreach($nationalities as $nationality)
                                            <option value="{{ $nationality->id }}" {{ old('nationality_id', $student->nationality_id) == $nationality->id ? 'selected' : '' }}>
                                                {{ $nationality->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">العنوان</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3">{{ old('address', $student->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_profile_public"
                                               name="is_profile_public" value="1"
                                               {{ old('is_profile_public', $student->is_profile_public) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="is_profile_public">
                                            إظهار ملفي الشخصي لبقية الطلاب في الواجهة الأمامية
                                        </label>
                                        <div class="form-text">
                                            عند التفعيل يمكن للطلاب الآخرين مشاهدة صفحتك في قائمة الطلاب. عند الإلغاء يظل ملفك خاصًا.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-2"></i>حفظ التغييرات
                                </button>
                                <a href="{{ route('student.profile.index') }}" class="btn btn-secondary">إلغاء</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card" id="photo-section">
                        <div class="card-header">
                            <h5><i class="fa fa-image me-2"></i>الصورة الشخصية</h5>
                        </div>
                        <div class="card-body text-center">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                    <i class="fa fa-camera fa-3x text-muted"></i>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo" accept="image/*">
                            <small class="text-muted d-block mt-1">الحد الأقصى: 2MB</small>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-xl-8">
                <div class="card" id="password-section">
                    <div class="card-header">
                        <h5><i class="fa fa-key me-2"></i>تغيير كلمة المرور</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('student.profile.change-password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required>
                                    <small class="text-muted d-block mt-1">8 أحرف على الأقل، حروف كبيرة وصغيرة، أرقام ورموز</small>
                                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="new_password_confirmation" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-key me-2"></i>تغيير كلمة المرور
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
