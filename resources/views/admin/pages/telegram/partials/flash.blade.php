<div id="tg-flash-area">
    @if(session('success'))
        <div class="alert alert-success tg-flash-alert shadow-sm border-0 mb-3">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger tg-flash-alert shadow-sm border-0 mb-3">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
        </div>
    @endif
</div>
