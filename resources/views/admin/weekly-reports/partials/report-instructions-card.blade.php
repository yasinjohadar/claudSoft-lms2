@if(filled($report->report_description))
    <div class="card custom-card border-primary-transparent dashboard-fade-in mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <div class="avatar avatar-md bg-primary-transparent text-primary rounded-circle flex-shrink-0">
                    <i class="fe fe-clipboard fs-18"></i>
                </div>
                <div class="flex-fill">
                    <h6 class="fw-semibold mb-3">المطلوب منك إنجازه</h6>
                    <div class="p-3 rounded border bg-light weekly-report-html-content">
                        {!! $report->report_description !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
