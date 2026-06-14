@if($sessions->count() > 0)
    <form id="bulkDeleteForm" action="{{ route('admin.user-sessions.bulk-delete') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>تاريخ البدء</th>
                        <th>المدة</th>
                        <th>الجهاز</th>
                        <th>الموقع</th>
                        <th>الحالة</th>
                        <th>الأنشطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input session-checkbox" value="{{ $session->id }}" onchange="updateBulkActionBar()">
                            </td>
                            <td>{{ $sessions->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($session->user)
                                        @if($session->user->avatar)
                                            <img src="{{ asset('storage/' . $session->user->avatar) }}"
                                                 alt="{{ $session->user->name }}"
                                                 class="avatar avatar-sm rounded-circle me-2">
                                        @else
                                            <div class="avatar avatar-sm rounded-circle bg-primary-transparent me-2">
                                                <span class="fw-bold">{{ substr($session->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $session->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $session->user->email }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $session->started_at->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <span class="badge bg-info-transparent text-info">
                                    {{ $session->duration_formatted }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $session->device_info }}</small>
                                @if($session->ip_address)
                                    <br>
                                    <small class="text-muted">{{ $session->ip_address }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $session->location_formatted }}</small>
                            </td>
                            <td>
                                @if($session->status == 'active')
                                    <span class="badge bg-success">نشطة</span>
                                @elseif($session->status == 'completed')
                                    <span class="badge bg-info">مكتملة</span>
                                @elseif($session->status == 'disconnected')
                                    <span class="badge bg-warning">منفصلة</span>
                                @else
                                    <span class="badge bg-secondary">انتهت</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-transparent text-primary">
                                    {{ $session->activities_count }} نشاط
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.user-sessions.show', $session->id) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-4">
        {{ $sessions->withQueryString()->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <p class="text-muted">لا توجد جلسات</p>
    </div>
@endif
