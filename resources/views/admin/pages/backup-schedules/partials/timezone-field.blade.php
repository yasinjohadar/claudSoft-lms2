@php
    /** @var string|null $selectedTimezone */
    $appTimezone = (string) config('app.timezone', 'UTC');

    $timezoneOptions = [
        'Asia/Damascus'   => 'دمشق (سوريا)',
        'Asia/Riyadh'     => 'الرياض (السعودية)',
        'Asia/Dubai'      => 'دبي (الإمارات)',
        'Asia/Amman'      => 'عمّان (الأردن)',
        'Asia/Beirut'     => 'بيروت (لبنان)',
        'Asia/Baghdad'    => 'بغداد (العراق)',
        'Asia/Qatar'      => 'الدوحة (قطر)',
        'Asia/Kuwait'     => 'الكويت',
        'Africa/Cairo'    => 'القاهرة (مصر)',
        'Africa/Tripoli'  => 'طرابلس (ليبيا)',
        'Africa/Tunis'    => 'تونس',
        'Africa/Algiers'  => 'الجزائر',
        'Africa/Casablanca' => 'الدار البيضاء (المغرب)',
        'Europe/Istanbul' => 'إسطنبول (تركيا)',
        'Europe/London'   => 'لندن',
        'UTC'             => 'UTC (التوقيت العالمي)',
    ];

    // احتفظ بأي توقيت محفوظ سابقاً حتى لو لم يكن ضمن القائمة
    $current = $selectedTimezone ?: $appTimezone;
    if ($current && ! array_key_exists($current, $timezoneOptions)) {
        $timezoneOptions[$current] = $current;
    }
@endphp

<div class="col-md-6">
    <label for="timezone" class="form-label">
        التوقيت <span class="text-danger">*</span>
    </label>
    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone" required>
        @foreach($timezoneOptions as $tzValue => $tzLabel)
            <option value="{{ $tzValue }}" {{ old('timezone', $current) === $tzValue ? 'selected' : '' }}>
                {{ $tzLabel }} ({{ $tzValue }})
            </option>
        @endforeach
    </select>
    @error('timezone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">
        الوقت أعلاه يُفسَّر بهذا التوقيت. توقيت الخادم الحالي: <code>{{ $appTimezone }}</code>.
    </small>
</div>
