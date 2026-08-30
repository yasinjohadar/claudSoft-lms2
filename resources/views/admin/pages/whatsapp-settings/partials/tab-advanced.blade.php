{{-- تبويب: متقدم --}}
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Timeout (بالثواني)</label>
        <input type="number"
               class="form-control"
               name="timeout"
               id="timeout"
               value="{{ old('timeout', $settings['timeout'] ?? 30) }}"
               min="1"
               max="300"
               placeholder="30">
        <small class="text-muted">المهلة الزمنية لانتظار استجابة API</small>
        @error('timeout')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
