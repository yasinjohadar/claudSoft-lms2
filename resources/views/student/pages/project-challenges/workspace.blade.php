@extends('student.layouts.master')

@section('page-title')
    مساحة العمل — {{ $team->name }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            @php
                $linkTypes = config('project_challenges.link_types', []);
                $submissionStatusLabels = [
                    'draft' => ['label' => 'مسودة', 'class' => 'draft'],
                    'submitted' => ['label' => 'مُرسَل', 'class' => 'submitted'],
                    'under_review' => ['label' => 'قيد المراجعة', 'class' => 'submitted'],
                    'approved' => ['label' => 'مقبول', 'class' => 'approved'],
                    'rejected' => ['label' => 'مرفوض', 'class' => 'rejected'],
                    'resubmit_required' => ['label' => 'إعادة تسليم', 'class' => 'resubmit'],
                ];
                $activityLabels = [
                    'team.created' => 'تم إنشاء الفريق',
                    'team.activated' => 'تم تفعيل الفريق',
                    'team.member_joined' => 'انضم عضو جديد',
                    'team.join_requested' => 'طلب انضمام',
                    'team.join_rejected' => 'رفض طلب انضمام',
                    'team.invitation_sent' => 'دعوة مرسلة',
                    'team.member_removed' => 'إزالة عضو',
                    'stage.submitted' => 'تسليم مرحلة',
                    'stage.graded' => 'تقييم مرحلة',
                    'showcase.published' => 'نشر عرض',
                    'showcase.unpublished' => 'إلغاء نشر العرض',
                ];
                $canPublishShowcase = app(\App\Services\ProjectChallenge\ProjectShowcaseService::class)->canPublish($team);

                $activeStageData = null;
                foreach ($stages as $item) {
                    if (!$item['unlocked']) continue;
                    $sub = $item['submission'];
                    $editable = !$sub || in_array($sub->status, ['draft', 'resubmit_required'], true);
                    if ($editable) {
                        $activeStageData = $item;
                        break;
                    }
                }
            @endphp

            <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a href="{{ route('student.project-challenges.show', $team->challenge->id) }}" class="text-muted">
                    <i class="fe fe-arrow-right me-1"></i>{{ $team->challenge->title }}
                </a>
                @if($canPublishShowcase && (!$team->showcase || !$team->showcase->isPublished()))
                    <a href="{{ route('student.project-teams.showcase.publish-form', $team->id) }}" class="btn btn-warning btn-sm">
                        <i class="fe fe-upload me-1"></i>نشر في المعرض
                    </a>
                @elseif($team->showcase?->isPublished())
                    <a href="{{ route('student.community-projects.show', $team->showcase->slug) }}" class="btn btn-outline-success btn-sm">
                        <i class="fe fe-eye me-1"></i>عرض المشروع المنشور
                    </a>
                @endif
            </div>

            <div class="pc-team-header">
                <div class="pc-team-avatar">{{ mb_substr($team->name, 0, 1) }}</div>
                <div class="flex-fill">
                    <h4 class="mb-1">{{ $team->name }}</h4>
                    @if($team->description)
                        <p class="text-muted mb-2 small">{{ $team->description }}</p>
                    @endif
                    <div class="pc-members">
                        @foreach($team->activeMembers as $member)
                            <span class="pc-member-chip @if($member->user_id === $team->leader_id) pc-member-chip--leader @endif">
                                <i class="fe fe-user"></i>
                                {{ $member->user->name ?? $member->user->email }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-muted small">الدرجة</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($team->total_score, 1) }}</div>
                </div>
            </div>

            <div class="pc-progress-wrap">
                <div class="pc-progress-header">
                    <span class="pc-progress-label">تقدم الفريق</span>
                    <span class="pc-progress-value">{{ number_format($team->progress_percent, 0) }}%</span>
                </div>
                <div class="pc-progress">
                    <div class="pc-progress__bar" style="width:{{ min(100, (float)$team->progress_percent) }}%"></div>
                </div>
            </div>

            <div class="pc-workspace">
                <div class="pc-workspace-main">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">مراحل المشروع</div></div>
                        <div class="card-body">
                            <div class="pc-timeline">
                                @foreach($stages as $item)
                                    @php
                                        $stage = $item['stage'];
                                        $submission = $item['submission'];
                                        $unlocked = $item['unlocked'];
                                        $isActive = $activeStageData && $activeStageData['stage']->id === $stage->id;
                                        $timelineClass = 'pc-timeline-item';
                                        if ($submission?->isApproved()) $timelineClass .= ' pc-timeline-item--completed';
                                        elseif ($isActive) $timelineClass .= ' pc-timeline-item--active';
                                        elseif (!$unlocked) $timelineClass .= ' pc-timeline-item--locked';
                                    @endphp
                                    <div class="{{ $timelineClass }}">
                                        <div class="pc-timeline-item__dot"></div>
                                        <div class="pc-timeline-item__card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">{{ $stage->title }}</h6>
                                                <div class="d-flex gap-1">
                                                    @if($stage->is_optional)
                                                        <span class="badge bg-secondary-transparent">اختياري</span>
                                                    @endif
                                                    @if(!$unlocked)
                                                        <span class="pc-stage-status pc-stage-status--locked">مقفلة</span>
                                                    @elseif($submission)
                                                        @php $st = $submissionStatusLabels[$submission->status] ?? ['label' => $submission->status, 'class' => 'draft']; @endphp
                                                        <span class="pc-stage-status pc-stage-status--{{ $st['class'] }}">{{ $st['label'] }}</span>
                                                    @else
                                                        <span class="pc-stage-status pc-stage-status--draft">لم يبدأ</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($stage->description)
                                                <p class="text-muted small mb-2">{{ $stage->description }}</p>
                                            @endif
                                            @if($submission && $submission->links->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    @foreach($submission->links as $link)
                                                        <a href="{{ $link->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fe fe-link me-1"></i>{{ $link->title ?: ($linkTypes[$link->link_type] ?? $link->link_type) }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($submission?->feedback)
                                                <div class="alert alert-info mt-2 mb-0 small py-2">
                                                    <strong>ملاحظات المقيّم:</strong> {{ $submission->feedback }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($activeStageData)
                        @php
                            $activeStage = $activeStageData['stage'];
                            $activeSubmission = $activeStageData['submission'];
                            $existingLinks = $activeSubmission?->links ?? collect();
                            if ($existingLinks->isEmpty()) {
                                $existingLinks = collect([['link_type' => '', 'title' => '', 'url' => '']]);
                            }
                            $allowedTypes = $activeStage->getAllowedLinkTypes();
                        @endphp
                        <div class="card custom-card border-primary">
                            <div class="card-header bg-primary-transparent">
                                <div class="card-title text-primary">
                                    <i class="fe fe-edit me-1"></i>تسليم المرحلة: {{ $activeStage->title }}
                                </div>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('student.project-teams.save-draft', [$team->id, $activeStage->id]) }}" method="POST" id="stage-links-form">
                                    @csrf
                                    <div class="pc-link-rows" id="link-rows">
                                        @foreach($existingLinks as $i => $link)
                                            <div class="pc-link-row">
                                                <div>
                                                    <label class="form-label small">النوع</label>
                                                    <select name="links[{{ $i }}][link_type]" class="form-select form-select-sm" required>
                                                        <option value="">—</option>
                                                        @foreach($linkTypes as $typeKey => $typeLabel)
                                                            @if(in_array($typeKey, $allowedTypes))
                                                                <option value="{{ $typeKey }}" @selected(($link->link_type ?? $link['link_type'] ?? '') === $typeKey)>{{ $typeLabel }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label small">العنوان</label>
                                                    <input type="text" name="links[{{ $i }}][title]" class="form-control form-control-sm"
                                                           value="{{ $link->title ?? $link['title'] ?? '' }}">
                                                </div>
                                                <div>
                                                    <label class="form-label small">الرابط <span class="text-danger">*</span></label>
                                                    <input type="url" name="links[{{ $i }}][url]" class="form-control form-control-sm" required
                                                           value="{{ $link->url ?? $link['url'] ?? '' }}" placeholder="https://">
                                                </div>
                                                <div class="pt-4">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-link-btn" @if($existingLinks->count() <= 1) disabled @endif>
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>
                                                <input type="hidden" name="links[{{ $i }}][sort_order]" value="{{ $i }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="add-link-btn">
                                        <i class="fe fe-plus me-1"></i>إضافة رابط
                                    </button>
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fe fe-save me-1"></i>حفظ مسودة
                                        </button>
                                    </div>
                                </form>
                                <form action="{{ route('student.project-teams.submit-stage', [$team->id, $activeStage->id]) }}" method="POST" class="mt-2"
                                      onsubmit="return confirm('تأكيد تسليم هذه المرحلة؟ لن تتمكن من التعديل حتى يراجع المقيّم.')">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-send me-1"></i>تسليم المرحلة
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif($team->progress_percent >= 100)
                        <div class="alert alert-success">
                            <i class="fe fe-check-circle me-1"></i>أكملتم جميع المراحل! يمكنكم نشر مشروعكم في المعرض.
                        </div>
                    @endif
                </div>

                <div class="pc-workspace-sidebar">
                    <div class="card custom-card">
                        <div class="card-header"><div class="card-title">سجل النشاط</div></div>
                        <div class="card-body p-0">
                            <div class="pc-activity-feed px-3">
                                @forelse($team->activities->take(30) as $activity)
                                    <div class="pc-activity-item">
                                        <div class="pc-activity-icon"><i class="fe fe-activity"></i></div>
                                        <div>
                                            <div class="pc-activity-text">
                                                {{ $activityLabels[$activity->event_type] ?? $activity->event_type }}
                                                @if($activity->actor)
                                                    <span class="text-muted">— {{ $activity->actor->name }}</span>
                                                @endif
                                            </div>
                                            <div class="pc-activity-time">{{ $activity->created_at?->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center py-4 small">لا يوجد نشاط بعد</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="link-row-template">
        <div class="pc-link-row">
            <div>
                <label class="form-label small">النوع</label>
                <select name="links[__INDEX__][link_type]" class="form-select form-select-sm" required>
                    <option value="">—</option>
                    @if(isset($allowedTypes))
                        @foreach($linkTypes as $typeKey => $typeLabel)
                            @if(in_array($typeKey, $allowedTypes))
                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endif
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label class="form-label small">العنوان</label>
                <input type="text" name="links[__INDEX__][title]" class="form-control form-control-sm">
            </div>
            <div>
                <label class="form-label small">الرابط <span class="text-danger">*</span></label>
                <input type="url" name="links[__INDEX__][url]" class="form-control form-control-sm" required placeholder="https://">
            </div>
            <div class="pt-4">
                <button type="button" class="btn btn-sm btn-outline-danger remove-link-btn"><i class="fe fe-trash-2"></i></button>
            </div>
            <input type="hidden" name="links[__INDEX__][sort_order]" value="__INDEX__">
        </div>
    </template>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('link-rows');
    const template = document.getElementById('link-row-template');
    const addBtn = document.getElementById('add-link-btn');
    if (!container || !template) return;

    function reindexLinks() {
        container.querySelectorAll('.pc-link-row').forEach((row, i) => {
            row.querySelectorAll('[name^="links["]').forEach(el => {
                el.name = el.name.replace(/links\[\d+\]/, 'links[' + i + ']');
            });
            const sortInput = row.querySelector('[name$="[sort_order]"]');
            if (sortInput) sortInput.value = i;
        });
        const rows = container.querySelectorAll('.pc-link-row');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-link-btn');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const index = container.querySelectorAll('.pc-link-row').length;
            const html = template.innerHTML.replace(/__INDEX__/g, index);
            container.insertAdjacentHTML('beforeend', html);
            reindexLinks();
        });
    }

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-link-btn')) {
            const rows = container.querySelectorAll('.pc-link-row');
            if (rows.length <= 1) return;
            e.target.closest('.pc-link-row').remove();
            reindexLinks();
        }
    });
});
</script>
@endpush
