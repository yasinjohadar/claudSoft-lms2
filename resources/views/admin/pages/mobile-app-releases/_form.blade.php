{{-- حقول الإصدار المشتركة بين الإنشاء والتعديل. --}}
<div class="card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="platform" class="form-label">المنصة <span class="text-danger">*</span></label>
            <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
                @foreach (\App\Models\MobileAppRelease::PLATFORMS as $key => $label)
                    <option value="{{ $key }}" @selected(old('platform', $release->platform) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('platform')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label for="version_name" class="form-label">رقم الإصدار <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('version_name') is-invalid @enderror" id="version_name" name="version_name"
                   value="{{ old('version_name', $release->version_name) }}" placeholder="1.1.0" dir="ltr" required>
            @error('version_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted">الرقم الذي يراه الطالب (version في pubspec.yaml).</small>
        </div>

        <div class="col-md-4">
            <label for="build_number" class="form-label">رقم البناء <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('build_number') is-invalid @enderror" id="build_number" name="build_number"
                   value="{{ old('build_number', $release->build_number ?? $nextBuild) }}" min="1" dir="ltr" required>
            @error('build_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted">الرقم بعد + في pubspec.yaml (مثال 1.1.0+<strong>2</strong>) — يجب أن يزيد مع كل إصدار.</small>
        </div>

        <div class="col-md-4">
            <label for="distribution" class="form-label">طريقة التوزيع <span class="text-danger">*</span></label>
            <select class="form-select @error('distribution') is-invalid @enderror" id="distribution" name="distribution" required>
                @foreach (\App\Models\MobileAppRelease::DISTRIBUTIONS as $key => $label)
                    <option value="{{ $key }}" @selected(old('distribution', $release->distribution) === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('distribution')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-8">
            <label for="download_url" class="form-label">رابط التحديث <span class="text-danger">*</span></label>
            <input type="url" class="form-control @error('download_url') is-invalid @enderror" id="download_url" name="download_url"
                   value="{{ old('download_url', $release->download_url) }}" dir="ltr" required>
            @error('download_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted" id="download_url_hint"></small>
        </div>

        <div class="col-12">
            <label for="release_notes" class="form-label">ما الجديد في هذا الإصدار؟</label>
            <textarea class="form-control @error('release_notes') is-invalid @enderror" id="release_notes" name="release_notes" rows="5"
                      placeholder="كل سطر يظهر كنقطة في نافذة التحديث، مثال:&#10;صفحة الإشعارات الجديدة&#10;تحسين سرعة تشغيل الفيديو&#10;إصلاح مشاكل في صفحة الفواتير">{{ old('release_notes', $release->release_notes) }}</textarea>
            @error('release_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" id="is_forced" name="is_forced" value="1" {{ old('is_forced', $release->is_forced) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_forced"><i class="fas fa-lock text-danger me-1"></i>تحديث إجباري</label>
                </div>
                <small class="text-muted">يمنع الطالب من استخدام التطبيق حتى يحدّث (للتغييرات الحرجة أو عند تغيّر الـ API). بدونه يمكن للطالب التأجيل.</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $release->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">نشط</label>
                </div>
                <small class="text-muted">الإصدارات الموقوفة لا تظهر للطلاب (مفيد للتجهيز قبل وصول التحديث إلى المتجر).</small>
            </div>
        </div>

        @if (! $release->exists)
            <div class="col-12">
                <div class="border border-primary rounded p-3 bg-primary-transparent">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" id="notify_now" name="notify_now" value="1" {{ old('notify_now') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="notify_now"><i class="fas fa-paper-plane text-primary me-1"></i>إرسال إشعار للطلاب الآن</label>
                    </div>
                    <small class="text-muted d-block mb-2">إشعار فوري داخل التطبيق يفتح نافذة التحديث. يمكنك إرساله لاحقاً من قائمة الإصدارات.</small>
                    <input type="hidden" name="app_users_only" value="0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="app_users_only" name="app_users_only" value="1" checked>
                        <label class="form-check-label small" for="app_users_only">فقط الطلاب الذين سجّلوا الدخول من التطبيق</label>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const platform = document.getElementById('platform');
        const distribution = document.getElementById('distribution');
        const url = document.getElementById('download_url');
        const hint = document.getElementById('download_url_hint');

        function refreshHint() {
            const direct = distribution.value === 'direct';
            const ios = platform.value === 'ios';
            url.placeholder = direct
                ? 'https://claudsoft.com/downloads/claudsoft-1.1.0.apk'
                : (ios ? 'https://apps.apple.com/app/id0000000000' : 'https://play.google.com/store/apps/details?id=com.claudsoft.app');
            hint.textContent = direct
                ? 'رابط مباشر لملف APK (أندرويد فقط). سيُفتح في المتصفح لتنزيله وتثبيته.'
                : (ios ? 'رابط التطبيق في App Store.' : 'رابط التطبيق في Google Play.');
        }

        platform.addEventListener('change', refreshHint);
        distribution.addEventListener('change', refreshHint);
        refreshHint();
    });
</script>
@stop
