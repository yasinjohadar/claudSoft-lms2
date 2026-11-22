{{-- لأي معسكر / دبلوم تعود هذه الفاتورة --}}
@php $programs = $invoice->relatedPrograms(); @endphp

@if($programs->isNotEmpty())
    <div class="student-invoice-programs {{ $class ?? '' }}">
        @foreach($programs as $program)
            <span class="student-invoice-program" title="{{ $program['type'] }}: {{ $program['name'] }}">
                <i class="fe {{ $program['icon'] }}"></i>
                <span class="student-invoice-program__type">{{ $program['type'] }}:</span>
                <span class="student-invoice-program__name">{{ $program['name'] }}</span>
            </span>
        @endforeach
    </div>
@elseif($invoice->items->first()?->description)
    <div class="student-invoice-programs {{ $class ?? '' }}">
        <span class="student-invoice-program student-invoice-program--plain" title="{{ $invoice->items->first()->description }}">
            <i class="fe fe-tag"></i>
            <span class="student-invoice-program__name">{{ $invoice->items->first()->description }}</span>
        </span>
    </div>
@endif
