@php
    $adminName = auth()->user()->name_ar ?? auth()->user()->name ?? 'مدير النظام';
@endphp

<div class="my-4 page-header-breadcrumb admin-dashboard-welcome">
    <h4 class="mb-1 admin-dashboard-welcome__title">مرحباً {{ $adminName }}، أهلاً بعودتك!</h4>
    <p class="mb-0 text-muted admin-dashboard-welcome__subtitle">أنت مسجل الدخول كـ أدمن</p>
</div>
