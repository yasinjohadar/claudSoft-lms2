@if(isset($isImpersonating) && $isImpersonating && isset($originalUser))
<div class="alert alert-warning alert-dismissible fade show mb-0 border-0 rounded-0" role="alert" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000; border-bottom: 3px solid #ff9800 !important;">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <i class="fas fa-user-secret me-2"></i>
                <strong>أنت تدخل كطالب:</strong>
                <span class="ms-2">{{ auth()->user()->name }}</span>
                <span class="text-muted ms-2">(الأدمن الأصلي: {{ $originalUser->name }})</span>
            </div>
            <div class="col-md-4 text-end">
                <form action="{{ route('admin.stop-impersonate') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        العودة إلى حساب الأدمن
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

