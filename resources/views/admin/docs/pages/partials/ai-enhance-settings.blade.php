@if(!empty($docsEngineChoiceAvailable))
    <div class="mb-3">
        <label class="form-label d-block">محرك التوثيق</label>
        <div class="doc-ai-engine-pills">
            <div class="doc-ai-engine-pill">
                <input type="radio" name="docs_engine" id="docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                <label for="docs_engine_laravel_ai"><i class="fe fe-cpu"></i>Laravel AI SDK</label>
            </div>
            <div class="doc-ai-engine-pill">
                <input type="radio" name="docs_engine" id="docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                <label for="docs_engine_legacy"><i class="fe fe-database"></i>موديلات قديمة</label>
            </div>
        </div>
    </div>
@endif

@if($models->isEmpty() && $laravelAiModels->isEmpty())
    <div class="alert alert-warning border-0 mb-0">لا يوجد موديل نشط.</div>
@else
    @if(!empty($docsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
        <div class="mb-3">
            <div id="docs_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                <label class="form-label" for="laravel_ai_model_id">موديل Laravel AI SDK</label>
                <select id="laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                    <option value="">افتراضي (docs.refine)</option>
                    @foreach($laravelAiModels as $lmodel)
                        <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
        <div class="mb-3">
            <div id="docs_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                <label class="form-label" for="ai_model_id">موديل AI</label>
                <select id="ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                    <option value="">الافتراضي</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
    <div class="mb-3">
        <label class="form-label" for="language">اللغة</label>
        <select id="language" class="form-select">
            <option value="ar" selected>العربية</option>
            <option value="en">English</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="tone">الأسلوب</label>
        <select id="tone" class="form-select">
            <option value="professional" selected>احترافي</option>
            <option value="friendly">ودود</option>
            <option value="technical">تقني</option>
            <option value="casual">عادي</option>
            <option value="formal">رسمي</option>
        </select>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="update_excerpt">
        <label class="form-check-label" for="update_excerpt">تحديث المقتطف (excerpt)</label>
    </div>
@endif
