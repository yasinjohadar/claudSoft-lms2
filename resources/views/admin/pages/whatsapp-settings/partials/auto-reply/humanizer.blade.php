{{--
    محاكاة السلوك البشري — هذه الحقول تُحدد زمن وصول الرد، وهي تراكمية:
    debounce + تأخير القراءة + (مدة الكتابة × الأجزاء) + (فاصل الإرسال × الأجزاء).
    راجع «ميزانية التأخير» في: php artisan whatsapp:autoreply-doctor
--}}
<div class="col-12 mb-2">
    <h6 class="fw-semibold text-muted"><i class="ri-time-line me-1"></i>محاكاة السلوك البشري</h6>
    <small class="text-muted d-block">
        كل قيمة هنا تُضاف إلى زمن وصول الرد. خفّضها لتسريع الرد، ولا تُصفّرها كلها —
        الرد الفوري المتكرر قد يُعرّض الرقم للحظر.
    </small>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">تأخير قراءة (من — ث)</label>
    <input type="number" class="form-control" name="auto_reply_initial_delay_min" min="0" max="30" value="{{ old('auto_reply_initial_delay_min', $settings['auto_reply_initial_delay_min'] ?? 2) }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">تأخير قراءة (إلى — ث)</label>
    <input type="number" class="form-control" name="auto_reply_initial_delay_max" min="0" max="60" value="{{ old('auto_reply_initial_delay_max', $settings['auto_reply_initial_delay_max'] ?? 5) }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">مدة «يكتب...» (ث)</label>
    <input type="number" class="form-control" name="auto_reply_typing_duration" min="1" max="15" value="{{ old('auto_reply_typing_duration', $settings['auto_reply_typing_duration'] ?? 3) }}">
    <small class="text-muted">تُضرب في عدد الأجزاء</small>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Debounce (ث)</label>
    <input type="number" class="form-control" name="auto_reply_debounce_seconds" min="1" max="60" value="{{ old('auto_reply_debounce_seconds', $settings['auto_reply_debounce_seconds'] ?? 8) }}">
    <small class="text-muted">انتظار رسائل متتابعة</small>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">Cooldown للرقم (ث)</label>
    <input type="number" class="form-control" name="auto_reply_contact_cooldown" min="10" max="600" value="{{ old('auto_reply_contact_cooldown', $settings['auto_reply_contact_cooldown'] ?? 45) }}">
    <small class="text-muted">أقل فاصل بين ردَّين لنفس الرقم</small>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">أقصى أجزاء للرد</label>
    <input type="number" class="form-control" name="auto_reply_max_chunks" min="1" max="5" value="{{ old('auto_reply_max_chunks', $settings['auto_reply_max_chunks'] ?? 3) }}">
    <small class="text-muted">ما يتجاوزها من الرد يُحذف</small>
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">حد أحرف/جزء</label>
    <input type="number" class="form-control" name="auto_reply_chunk_max_chars" min="100" max="1000" value="{{ old('auto_reply_chunk_max_chars', $settings['auto_reply_chunk_max_chars'] ?? 350) }}">
</div>
<div class="col-md-3 mb-3">
    <label class="form-label">رقم اختبار</label>
    <input type="text" class="form-control" name="auto_reply_test_phone" dir="ltr" placeholder="9665..." value="{{ old('auto_reply_test_phone', $settings['auto_reply_test_phone'] ?? '') }}">
</div>
