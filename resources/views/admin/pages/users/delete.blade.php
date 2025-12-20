<!-- Delete User Modal -->

<div class="modal fade" id="delete{{$user->id}}" tabindex="-1" aria-labelledby="deleteUserModalLabel{{$user->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <form method="POST" action="{{route('users.destroy' , 'test')}}">
                    @csrf
                    @method('DELETE')
                    
                    <!-- Icon -->
                    <div class="mb-4">
                        <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-slash fa-3x"></i>
                        </span>
                    </div>

                    <!-- Title -->
                    <h5 class="modal-title mb-3 fw-bold" id="deleteUserModalLabel{{$user->id}}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        تأكيد حذف المستخدم
                    </h5>

                    <!-- Message -->
                    <p class="text-muted mb-4">
                        هل أنت متأكد من حذف المستخدم
                        <strong class="text-danger d-block mt-2 fs-5">{{$user->name}}</strong>؟
                        <br>
                        <small class="text-danger mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            سيتم حذف جميع البيانات المرتبطة بهذا المستخدم ولا يمكن التراجع عن هذه العملية.
                        </small>
                    </p>

                    <input type="hidden" name="id" value="{{$user->id}}">

                    <!-- Actions -->
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
