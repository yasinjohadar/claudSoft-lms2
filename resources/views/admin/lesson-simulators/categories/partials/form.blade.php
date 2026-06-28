<div class="card shadow-sm border-0">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ $action }}" method="POST">
            @csrf
            @if($method !== 'POST') @method($method) @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم التصنيف <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}" dir="ltr" placeholder="يُولَّد تلقائياً">
                </div>
                <div class="col-md-6">
                    <label class="form-label">التصنيف الأب</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— تصنيف رئيسي —</option>
                        @foreach($parentOptions as $id => $label)
                            <option value="{{ $id }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">يمكنك إنشاء عدة مستويات: رئيسي ← فرعي ← فرعي...</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="cat-active" @checked(old('is_active', $category->is_active ?? true))>
                        <label class="form-check-label" for="cat-active">نشط</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
