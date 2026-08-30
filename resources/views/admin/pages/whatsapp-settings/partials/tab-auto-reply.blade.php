{{--
    تبويب: الرد التلقائي — ملف تركيب. أي مجموعة حقول جديدة تُضاف كـ partial فرعي
    تحت partials/auto-reply/ وتُدرَج هنا بسطر @include واحد.
--}}
<div class="alert alert-info small mb-3">
    <i class="ri-information-line me-1"></i>
    <strong>Evolution API:</strong> يعمل الرد التلقائي على المحادثات <strong>الفردية</strong> الواردة عبر Webhook
    (<code>MESSAGES_UPSERT</code>). تأكد من:
    <ul class="mb-0 mt-1">
        <li>تفعيل WhatsApp + اختيار Evolution كمزود</li>
        <li>ضبط Webhook من <a href="{{ route('admin.evolution-api.webhook.index') }}">لوحة Evolution → Webhook</a></li>
        <li>تشغيل عامل الطابور على الطابور <code>{{ config('whatsapp.queue', 'whatsapp') }}</code></li>
    </ul>
    <div class="mt-2">
        للتشخيص: <a href="{{ route('admin.evolution-api.webhook.index') }}"><strong>زر «تشخيص مسار الرد التلقائي»</strong></a>
        أو الأمر <code>php artisan whatsapp:autoreply-doctor</code>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="auto_reply"
                   id="auto_reply"
                   value="1"
                   {{ ($settings['auto_reply'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="auto_reply">
                <strong>تفعيل الرد التلقائي</strong>
            </label>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Instance الدعم (Evolution)</label>
        <select class="form-select" name="auto_reply_evolution_instance" id="auto_reply_evolution_instance">
            <option value="">— اختر رقم/Instance الدعم —</option>
            @foreach($evolutionInstances ?? [] as $inst)
                <option value="{{ $inst->instance_name }}" {{ ($settings['auto_reply_evolution_instance'] ?? '') === $inst->instance_name ? 'selected' : '' }}>
                    {{ $inst->instance_name }}
                    @if($inst->phone_number) ({{ $inst->phone_number }}) @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            الرد التلقائي يعمل فقط على هذا الرقم. إن تُرك فارغاً يُستخدم Instance الافتراضي من إعدادات Evolution.
            <a href="{{ route('admin.evolution-api.instances.index') }}">إدارة Instances</a>
        </small>
        @error('auto_reply_evolution_instance')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror

        @php
            $configuredInstance = $settings['auto_reply_evolution_instance'] ?? '';
            $instanceExists = $configuredInstance === '' || collect($evolutionInstances ?? [])
                ->contains(fn ($i) => $i->instance_name === $configuredInstance);
        @endphp
        @unless($instanceExists)
            <div class="alert alert-danger small mt-2 mb-0">
                <i class="ri-error-warning-line me-1"></i>
                الـ Instance المحفوظ «{{ $configuredInstance }}» غير موجود في القائمة — لن تصل أي رسالة.
                اختر واحداً من القائمة واحفظ.
            </div>
        @endunless
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label">أسئلة شائعة (FAQ) للذكاء الاصطناعي</label>
        <textarea class="form-control" name="auto_reply_faq_context" id="auto_reply_faq_context" rows="6" placeholder="مثال:&#10;س: كيف أسجّل في كورس؟&#10;ج: من الموقع الرسمي للأكاديمية...">{{ old('auto_reply_faq_context', $settings['auto_reply_faq_context'] ?? '') }}</textarea>
        <small class="text-muted">معلومات عامة عن الأكاديمية — بدون بيانات شخصية للطلاب.</small>
        @error('auto_reply_faq_context')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @include('admin.pages.whatsapp-settings.partials.auto-reply.humanizer')

    @include('admin.pages.whatsapp-settings.partials.auto-reply.tester')

    <div class="col-md-12 mb-3">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox"
                   name="auto_reply_use_ai"
                   id="auto_reply_use_ai"
                   value="1"
                   {{ ($settings['auto_reply_use_ai'] ?? false) ? 'checked' : '' }}
                   onchange="toggleAutoReplyAiFields(this.checked)">
            <label class="form-check-label" for="auto_reply_use_ai">
                <strong>استخدام الذكاء الاصطناعي للرد التلقائي</strong>
            </label>
        </div>
        <small class="text-muted">عند التفعيل، سيتم توليد الرد تلقائياً باستخدام أحد موديلات الذكاء الاصطناعي في النظام بدلاً من رسالة ثابتة.</small>
    </div>

    <div id="auto_reply_ai_fields" class="row" style="display: {{ ($settings['auto_reply_use_ai'] ?? false) ? 'flex' : 'none' }};">
        <div class="col-md-6 mb-3">
            <label class="form-label">موديل الذكاء الاصطناعي</label>
            <select class="form-select" name="auto_reply_ai_model_id" id="auto_reply_ai_model_id">
                <option value="">— اختر الموديل (أو الافتراضي) —</option>
                @foreach($aiModels ?? [] as $model)
                    <option value="{{ $model->id }}" {{ (string)($settings['auto_reply_ai_model_id'] ?? '') === (string)$model->id ? 'selected' : '' }}>
                        {{ $model->name }} ({{ $model->provider }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">إن لم تختر موديلاً، يُستخدم أفضل موديل متاح للدردشة.</small>
            @error('auto_reply_ai_model_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-12 mb-3">
            <label class="form-label">رسالة النظام (اختياري)</label>
            <textarea class="form-control"
                      name="auto_reply_ai_system_prompt"
                      id="auto_reply_ai_system_prompt"
                      rows="4"
                      placeholder="أنت مساعد ودود يرد على رسائل الواتساب نيابة عن منصة تعليمية. أجب بشكل مختصر ومهذب بالعربية.">{{ old('auto_reply_ai_system_prompt', $settings['auto_reply_ai_system_prompt'] ?? '') }}</textarea>
            <small class="text-muted">تحدد سلوك الرد التلقائي. اتركه فارغاً لاستخدام الوصف الافتراضي.</small>
            @error('auto_reply_ai_system_prompt')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-12 mb-3" id="auto_reply_fixed_message_wrap">
        <label class="form-label">رسالة الرد التلقائي <span id="auto_reply_message_label_extra" class="text-muted">(تُستخدم عند عدم تفعيل الذكاء الاصطناعي أو عند فشل التوليد)</span></label>
        <textarea class="form-control"
                  name="auto_reply_message"
                  id="auto_reply_message"
                  rows="3"
                  placeholder="شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.">{{ old('auto_reply_message', $settings['auto_reply_message'] ?? 'شكراً لك، تم استلام رسالتك. سنرد عليك قريباً.') }}</textarea>
        @error('auto_reply_message')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
