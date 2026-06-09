<div class="my-4 page-header-breadcrumb">
    <nav>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.weekly-reports.groups-overview') }}">التقارير الأسبوعية</a>
            </li>
            @if(!empty($current))
                <li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>
            @endif
        </ol>
    </nav>
</div>
