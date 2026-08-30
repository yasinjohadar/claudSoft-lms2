{{-- أداة فحص الرد التلقائي — المعالجات في @section('scripts') بملف index --}}
<div class="col-md-12 mb-3">
    <div class="card border bg-light">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="ri-flask-line me-1"></i>فحص الرد التلقائي</h6>
            <div class="mb-3">
                <label class="form-label">سؤال تجريبي</label>
                <textarea class="form-control" id="auto_reply_test_question" rows="2" placeholder="مثال: ما مواعيد الدعم الفني؟"></textarea>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn_auto_reply_preview">
                    <i class="ri-eye-line me-1"></i>معاينة الرد
                </button>
                <button type="button" class="btn btn-outline-success btn-sm" id="btn_auto_reply_test_send">
                    <i class="ri-send-plane-line me-1"></i>اختبار إرسال
                </button>
            </div>
            <div id="auto_reply_preview_result" class="d-none">
                <label class="form-label small text-muted">الرد الكامل</label>
                <pre class="bg-white border rounded p-2 small mb-2" id="auto_reply_preview_reply"></pre>
                <label class="form-label small text-muted">الأجزاء المرسلة</label>
                <ul class="small mb-0" id="auto_reply_preview_chunks"></ul>
            </div>
            <div id="auto_reply_test_status" class="small text-muted"></div>
        </div>
    </div>
</div>
