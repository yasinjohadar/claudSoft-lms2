@props([
    'countryCodeId' => 'auth_country_code_select',
    'phoneId' => 'phone',
    'phoneName' => 'phone',
    'countryCodeName' => 'country_code',
    'selectedCountryCode' => null,
    'phoneValue' => null,
    'phoneError' => null,
    'countryError' => null,
])

@php
    $selectedCountryCode = $selectedCountryCode ?? old('country_code', config('country_codes.default', '+963'));
    $phoneValue = $phoneValue ?? old('phone');
    $flagUrl = config('country_codes.flag_image_url', 'https://flagcdn.com/w20/{iso}.png');
@endphp

<div class="phone-country-block">
    <label class="phone-country-label">رقم الواتساب</label>
    <div class="phone-row">
        <div class="phone-row__code">
            <select name="{{ $countryCodeName }}"
                    id="{{ $countryCodeId }}"
                    class="form-control country-code-select @if($countryError) is-invalid @endif"
                    data-flag-url="{{ $flagUrl }}"
                    aria-label="رمز الدولة">
                @foreach(config('country_codes.list', []) as $code => $label)
                    @php
                        $isoList = config('country_codes.iso', []);
                        $iso = $isoList[$code] ?? '';
                        $textOnly = config('country_codes.list_text_only', [])[$code] ?? $label;
                        $separator = config('country_codes.separator', '  ·  ');
                        $display = $iso !== '' ? $textOnly . $separator . $iso : $textOnly;
                    @endphp
                    <option value="{{ $code }}" data-iso="{{ strtolower($iso) }}" {{ $selectedCountryCode == $code ? 'selected' : '' }}>
                        {{ $display }}
                    </option>
                @endforeach
            </select>
            @if($countryError)
                <div class="invalid-feedback d-block">{{ $countryError }}</div>
            @endif
        </div>
        <div class="phone-row__number">
            <input type="tel"
                   name="{{ $phoneName }}"
                   id="{{ $phoneId }}"
                   class="form-control @if($phoneError) is-invalid @endif"
                   value="{{ $phoneValue }}"
                   autocomplete="tel-national"
                   placeholder="5xxxxxxxx"
                   dir="ltr"
                   inputmode="numeric">
            @if($phoneError)
                <div class="invalid-feedback d-block">{{ $phoneError }}</div>
            @endif
        </div>
    </div>
    <div class="form-hint form-hint--warn">
        <strong>مهم:</strong> اختر الدولة من القائمة، ثم أدخل رقم الجوال <strong>بدون</strong> رمز الدولة و<strong>بدون</strong> صفر في البداية (مثال: 501234567).
    </div>
</div>
