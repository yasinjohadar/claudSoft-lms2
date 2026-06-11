@php
    $methods = $earningMethods ?? [];
    $preview = array_slice($methods, 0, 6);
@endphp

@if(count($preview) > 0)
    <div class="row g-3">
        @foreach($preview as $method)
            <div class="col-md-6">
                <div class="student-points-earn-item d-flex align-items-start gap-3 p-3 rounded">
                    <span class="student-points-earn-item__icon">
                        <i class="ri {{ $method['icon'] }}"></i>
                    </span>
                    <div class="flex-fill min-w-0">
                        <h6 class="mb-1 fw-semibold">{{ $method['title'] }}</h6>
                        <p class="text-muted fs-12 mb-2">{{ $method['description'] }}</p>
                        <span class="badge bg-primary-transparent">+{{ number_format($method['points']) }} نقطة</span>
                        @if(($method['xp'] ?? 0) > 0)
                            <span class="badge bg-success-transparent">+{{ $method['xp'] }} XP</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
