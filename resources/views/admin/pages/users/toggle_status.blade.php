<!-- Toggle User Status Modal -->

<div class="modal fade" id="toggleStatus{{ $user->id }}" tabindex="-1" aria-labelledby="toggleStatusLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5">
                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}">
                    @csrf

                    @if($user->is_active)
                        <!-- Deactivate User -->
                        <div class="text-center mb-4">
                            <span class="avatar avatar-xl bg-warning-transparent text-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-slash fa-3x"></i>
                            </span>
                        </div>

                        <h5 class="modal-title text-center mb-4 fw-bold" id="toggleStatusLabel{{ $user->id }}">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            إيقاف تفعيل المستخدم
                        </h5>

                        <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                            <div>
                                <strong>هل تريد إيقاف تفعيل المستخدم:</strong>
                                <div class="mt-2">
                                    <span class="badge bg-primary fs-6">{{ $user->name }}</span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    سيتم منع المستخدم من الدخول للنظام
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </button>
                            <button type="submit" class="btn btn-warning px-4">
                                <i class="fas fa-power-off me-2"></i>إيقاف التفعيل
                            </button>
                        </div>
                    @else
                        <!-- Activate User -->
                        <div class="text-center mb-4">
                            <span class="avatar avatar-xl bg-success-transparent text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-check fa-3x"></i>
                            </span>
                        </div>

                        <h5 class="modal-title text-center mb-4 fw-bold" id="toggleStatusLabel{{ $user->id }}">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            تفعيل المستخدم
                        </h5>

                        <div class="alert alert-success d-flex align-items-start mb-4" role="alert">
                            <i class="fas fa-check-circle me-2 fs-5 mt-1"></i>
                            <div>
                                <strong>هل تريد تفعيل المستخدم:</strong>
                                <div class="mt-2">
                                    <span class="badge bg-primary fs-6">{{ $user->name }}</span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    سيتمكن المستخدم من الدخول للنظام
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </button>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-power-off me-2"></i>تفعيل
                            </button>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-warning-transparent {
        background: rgba(255, 193, 7, 0.1) !important;
    }
    
    .bg-success-transparent {
        background: rgba(25, 135, 84, 0.1) !important;
    }
</style>
