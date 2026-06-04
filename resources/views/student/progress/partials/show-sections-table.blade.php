<div class="d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 student-progress-sections-table">
            <thead>
                <tr>
                    <th class="ps-4 fs-12">القسم</th>
                    <th class="fs-12">المحتوى المكتمل</th>
                    <th class="fs-12" style="min-width: 180px;">نسبة التقدم</th>
                    <th class="fs-12">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionsProgress as $sectionData)
                    <tr class="student-my-courses-stagger" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fe fe-folder text-primary"></i>
                                </span>
                                <strong class="fs-13">{{ $sectionData['section']->title }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-transparent fs-11">
                                {{ $sectionData['completed_modules'] }} / {{ $sectionData['total_modules'] }}
                            </span>
                        </td>
                        <td>
                            @include('student.progress.partials.show-section-progress', ['sectionData' => $sectionData])
                        </td>
                        <td>
                            @include('student.progress.partials.show-section-status', ['sectionData' => $sectionData])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
