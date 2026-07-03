@php
    $tabs = [
        ['route' => 'admin.telegram.settings.index', 'pattern' => 'admin.telegram.settings.*', 'label' => 'الإعدادات', 'icon' => 'ri-settings-3-line'],
        ['route' => 'admin.telegram.send', 'pattern' => 'admin.telegram.send*', 'label' => 'إرسال', 'icon' => 'ri-send-plane-line'],
        ['route' => 'admin.telegram.broadcast', 'pattern' => 'admin.telegram.broadcast|admin.telegram.broadcast.*', 'label' => 'بث جماعي', 'icon' => 'ri-megaphone-line'],
        ['route' => 'admin.telegram.broadcasts.index', 'pattern' => 'admin.telegram.broadcasts.*', 'label' => 'تقارير البث', 'icon' => 'ri-bar-chart-box-line'],
        ['route' => 'admin.telegram.templates.index', 'pattern' => 'admin.telegram.templates.*', 'label' => 'القوالب', 'icon' => 'ri-file-text-line'],
        ['route' => 'admin.telegram.groups.link', 'pattern' => 'admin.telegram.groups.link*', 'label' => 'ربط مجموعة', 'icon' => 'ri-link'],
        ['route' => 'admin.telegram.groups.post', 'pattern' => 'admin.telegram.groups.post*', 'label' => 'نشر', 'icon' => 'ri-chat-upload-line'],
        ['route' => 'admin.telegram.groups.compare', 'pattern' => 'admin.telegram.groups.compare*', 'label' => 'مقارنة', 'icon' => 'ri-git-merge-line'],
    ];
@endphp

<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-body py-3 px-3">
        <ul class="nav nav-pills nav-fill flex-wrap gap-1 tg-nav-pills" role="tablist">
            @foreach($tabs as $tab)
                @php $active = request()->routeIs($tab['pattern']); @endphp
                <li class="nav-item" role="presentation">
                    <a href="{{ route($tab['route']) }}"
                       class="nav-link rounded-pill px-3 py-2 {{ $active ? 'active' : 'text-muted' }}">
                        <i class="{{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
