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

        <div class="col-md-8" id="apk_upload_group">
            <label for="apk" class="form-label">ملف التطبيق (APK)</label>
            @if ($release->hasApk())
                <div class="alert alert-success py-2 px-3 mb-2 d-flex flex-wrap align-items-center gap-2 small">
                    <i class="fas fa-file-download"></i>
                    <span>ملف مرفوع: <strong dir="ltr">{{ $release->apkDownloadName() }}</strong> ({{ $release->apk_size_label }})</span>
                    <a href="{{ route('mobile-app.download.release', $release) }}" class="ms-auto">تنزيل للتجربة</a>
                </div>
            @endif
            <input type="file" class="form-control @error('apk') is-invalid @enderror" id="apk" name="apk" accept=".apk,application/vnd.android.package-archive">
            @error('apk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted">
                {{ $release->hasApk() ? 'اختر ملفاً جديداً لاستبدال الملف الحالي.' : 'ارفع ملف APK هنا وسيُنشأ رابط التنزيل تلقائياً.' }}
                أقصى حجم يسمح به الخادم حالياً: <strong dir="ltr">{{ ini_get('upload_max_filesize') }}</strong>.
            </small>
        </div>

        <div class="col-md-8" id="download_url_group">
            <label for="download_url" class="form-label" id="download_url_label">رابط التحديث</label>
            <input type="url" class="form-control @error('download_url') is-invalid @enderror" id="download_url" name="download_url"
                   value="{{ old('download_url', $release->hasApk() ? '' : $release->download_url) }}" dir="ltr">
            @error('download_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted" id="download_url_hint"></small>
            @if ($release->hasApk())
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="remove_apk" name="remove_apk" value="1">
                    <label class="form-check-label small" for="remove_apk">احذف الملف المرفوع واستخدم الرابط أعلاه بدلاً منه</label>
                </div>
            @endif
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

        const apkGroup = document.getElementById('apk_upload_group');
        const urlLabel = document.getElementById('download_url_label');

        function refreshHint() {
            const ios = platform.value === 'ios';
            if (ios && distribution.value === 'direct') distribution.value = 'store';
            distribution.querySelector('option[value="direct"]').disabled = ios;

            const direct = distribution.value === 'direct';
            apkGroup.style.display = direct ? '' : 'none';
            urlLabel.innerHTML = direct
                ? 'أو رابط خارجي لملف APK <span class="text-muted small">(اختياري إذا رفعت الملف)</span>'
                : 'رابط التحديث <span class="text-danger">*</span>';
            url.placeholder = direct
                ? 'https://example.com/claudsoft-1.1.0.apk'
                : (ios ? 'https://apps.apple.com/app/id0000000000' : 'https://play.google.com/store/apps/details?id=com.claudsoft.app');
            hint.textContent = direct
                ? 'استخدمه فقط إذا كان الملف مرفوعاً في مكان آخر (مثلاً لأن حجمه أكبر من حد الرفع).'
                : (ios ? 'رابط التطبيق في App Store.' : 'رابط التطبيق في Google Play.');
        }

        platform.addEventListener('change', refreshHint);
        distribution.addEventListener('change', refreshHint);
        refreshHint();
    });
</script>
@stop
