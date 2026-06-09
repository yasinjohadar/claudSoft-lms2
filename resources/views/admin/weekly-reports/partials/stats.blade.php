<div class="row g-3 dashboard-fade-in mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}{{ !empty($card['url']) ? ' position-relative admin-stats-card--clickable' : '' }}">
                @if(!empty($card['url']))
                    <a href="{{ $card['url'] }}" class="stretched-link text-decoration-none" aria-label="{{ $card['label'] }}"></a>
                @endif
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['prefix'])) data-countup-prefix="{{ $card['prefix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="2" @endif>0</h3>
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
