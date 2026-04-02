@if($notes->isEmpty())
    <p class="text-muted mb-0">لا توجد ملاحظات مسجلة لهذا المستخدم.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th style="width: 110px;">تاريخ الحدث</th>
                <th>الملاحظة</th>
                <th style="width: 130px;">المصدر</th>
                <th style="width: 140px;">سجّلها</th>
                <th style="width: 140px;">وقت التسجيل</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notes as $note)
                <tr>
                    <td>{{ $note->occurred_on?->format('Y-m-d') }}</td>
                    <td class="text-wrap" style="white-space: normal;">{{ $note->body }}</td>
                    <td>
                        @if($note->source === 'deactivation')
                            <span class="badge bg-warning-transparent text-warning">إيقاف تفعيل</span>
                        @elseif($note->source === 'reactivation')
                            <span class="badge bg-success-transparent text-success">تفعيل</span>
                        @else
                            <span class="badge bg-secondary-transparent text-secondary">{{ $note->source }}</span>
                        @endif
                    </td>
                    <td>{{ $note->creator?->name ?? '—' }}</td>
                    <td><small class="text-muted">{{ $note->created_at?->format('Y-m-d H:i') }}</small></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
