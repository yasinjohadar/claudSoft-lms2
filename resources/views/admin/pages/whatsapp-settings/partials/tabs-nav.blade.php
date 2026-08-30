{{--
    شريط تبويبات صفحة إعدادات WhatsApp.
    مصدر الحقيقة للتبويبات وحقول كل تبويب هو مصفوفة $tabs في index.blade.php —
    أي حقل جديد يجب تسجيله هناك وإلا لن يُفتح تبويبه تلقائياً عند فشل التحقق.
--}}
<ul class="nav nav-tabs nav-tabs-header mb-0 flex-wrap" id="whatsappSettingsTabs" role="tablist">
    @foreach($tabs as $key => $tab)
        <li class="nav-item" role="presentation">
            @if(isset($tab['link']))
                <a class="nav-link text-muted" href="{{ route($tab['link']) }}">
                    <i class="{{ $tab['icon'] }} me-2"></i>{{ $tab['label'] }}
                    <i class="ri-external-link-line ms-1 fs-11"></i>
                </a>
            @else
                <button class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
                        id="tab-{{ $key }}-btn"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-{{ $key }}"
                        data-tab-key="{{ $key }}"
                        type="button"
                        role="tab"
                        aria-controls="tab-{{ $key }}"
                        aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                    <i class="{{ $tab['icon'] }} me-2"></i>{{ $tab['label'] }}
                    @if($key === 'provider')
                        <span id="provider-tab-badge" class="badge bg-light text-dark ms-1"></span>
                    @endif
                    @if($tabHasError[$key] ?? false)
                        <span class="badge bg-danger ms-1" title="يوجد خطأ في هذا التبويب">
                            <i class="ri-error-warning-line"></i>
                        </span>
                    @endif
                </button>
            @endif
        </li>
    @endforeach
</ul>
