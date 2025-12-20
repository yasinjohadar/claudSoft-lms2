<!-- Change Password Modal -->

<div class="modal fade" id="change_password{{$user->id}}" tabindex="-1" aria-labelledby="changePasswordLabel{{$user->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5">
                <form method="POST" action="{{ route('users.update-password', $user->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Icon -->
                    <div class="text-center mb-4">
                        <span class="avatar avatar-xl bg-primary-transparent text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-key fa-3x"></i>
                        </span>
                    </div>

                    <!-- Title -->
                    <h5 class="modal-title text-center mb-4 fw-bold" id="changePasswordLabel{{$user->id}}">
                        <i class="fas fa-lock me-2 text-primary"></i>
                        تعديل كلمة المرور
                    </h5>

                    <!-- User Info -->
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-user-circle me-2 fs-5"></i>
                        <div>
                            <strong>المستخدم:</strong> {{ $user->name }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-4">
                        <label for="password{{$user->id}}" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>
                            كلمة المرور الجديدة
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password" 
                                   name="password" 
                                   id="password{{$user->id}}" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="أدخل كلمة المرور الجديدة"
                                   required>
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="password{{$user->id}}" onclick="togglePasswordVisibility('password{{$user->id}}', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            يجب أن تكون كلمة المرور 8 أحرف على الأقل
                        </small>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-4">
                        <label for="password_confirmation{{$user->id}}" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>
                            تأكيد كلمة المرور
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation{{$user->id}}" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   placeholder="أعد إدخال كلمة المرور"
                                   required>
                            <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="password_confirmation{{$user->id}}" onclick="togglePasswordVisibility('password_confirmation{{$user->id}}', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إغلاق
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>تعديل كلمة المرور
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-transparent {
        background: rgba(102, 126, 234, 0.1) !important;
    }
    
    .toggle-password-btn {
        cursor: pointer;
        border-left: none;
    }
    
    .toggle-password-btn:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
</style>

<script>
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (!input || !icon) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
