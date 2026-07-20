<div class="col-xl-12" id="bunny_library_field" style="display: none;">
    <label class="form-label">مكتبة Bunny Stream</label>
    @if($bunnyLibraries->isEmpty())
        <div class="alert alert-warning mb-2">
            لا توجد مكتبات Bunny مسجّلة.
            <a href="{{ route('bunny-stream-libraries.create') }}" class="alert-link">أضف مكتبة أولاً</a>
        </div>
    @endif
    <select name="bunny_stream_library_id" id="bunny_stream_library_id" class="form-select @error('bunny_stream_library_id') is-invalid @enderror" {{ $bunnyLibraries->isEmpty() ? 'disabled' : '' }}>
        <option value="">— اختر المكتبة —</option>
        @foreach($bunnyLibraries as $library)
            <option value="{{ $library->id }}"
                    data-library-id="{{ $library->library_id }}"
                    @selected(old('bunny_stream_library_id', $selectedLibraryId ?? null) == $library->id)>
                {{ $library->displayLabel() }}
            </option>
        @endforeach
    </select>
    <small class="text-muted d-block mt-1" id="bunny_library_hint">
        يُحدَّد تلقائياً من رابط الفيديو إن وُجدت المكتبة.
        <a href="{{ route('bunny-stream-libraries.index') }}" target="_blank">إدارة المكتبات</a>
    </small>
    @error('bunny_stream_library_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
