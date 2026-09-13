{{--
    Shared live batch progress table.

    Used by both the "start a batch" page and a batch's own detail page, so
    there is exactly one implementation of the polling loop, the status badges
    and the resume controls. The host page supplies $initialBatch (the status
    payload, or null) and everything else is driven by the JSON poller.
--}}
<div id="batchIdleMsg" class="doc-ai-hint mb-0" @if(!empty($initialBatch)) style="display:none" @endif>
    <i class="fe fe-info me-1"></i>
    لم تبدأ أي دفعة بعد. اكتب المواضيع واضغط «إضافة إلى الطابور».
</div>

<div id="batchProgressWrap" @if(empty($initialBatch)) style="display:none;" @endif>
    <div id="batchStalledAlert" class="alert alert-warning border-0 d-none">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="flex-grow-1">
                <i class="fe fe-alert-triangle me-1"></i>
                يبدو أن معالج الطابور متوقف — لم يبدأ أي موضوع منذ أكثر من 10 دقائق.
                شغّل <code>php artisan queue:work</code> ثم أعد تشغيل الدفعة.
            </span>
            <button type="button" class="btn btn-sm btn-warning" id="batchRestartBtn">
                <i class="fe fe-refresh-cw me-1"></i>إعادة تشغيل الدفعة
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <small class="text-muted" id="batchProgressLabel">في الطابور…</small>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary d-none" id="batchResumeAllBtn">
                <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                <i class="fe fe-play-circle me-1"></i>
                <span class="btn-text">متابعة كل الناقص</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="batchCancelBtn">
                <i class="fe fe-x me-1"></i>إلغاء الدفعة
            </button>
        </div>
    </div>

    <div class="progress mb-2" style="height: 8px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="batchProgressBar" role="progressbar" style="width: 0%"></div>
    </div>

    <div class="doc-ai-batch-summary mb-3" id="batchSummary"></div>

    <div class="table-responsive">
        <table class="doc-ai-batch-table">
            <thead>
                <tr>
                    <th style="width:38px;">#</th>
                    <th>الموضوع</th>
                    <th style="width:40%;">الحالة</th>
                    <th style="width:110px;"></th>
                </tr>
            </thead>
            <tbody id="batchItemsBody"></tbody>
        </table>
    </div>
</div>
