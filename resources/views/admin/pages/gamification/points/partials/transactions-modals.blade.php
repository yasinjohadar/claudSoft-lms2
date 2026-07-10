@php
    $earningCatalog = app(\App\Services\Gamification\PointEarningCatalog::class);
@endphp

@foreach ($transactions as $transaction)
    @php
        $modalSourceLabel = $earningCatalog->getSourceLabel($transaction->source);
    @endphp
    <div class="modal fade" id="deleteTransaction{{ $transaction->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إلغاء المعاملة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">هل تريد عكس هذه المعاملة؟</p>
                    <ul class="mb-0 text-muted fs-13">
                        <li>الطالب: {{ $transaction->user->name ?? '—' }}</li>
                        <li>النقاط: {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}</li>
                        <li>المصدر: {{ $modalSourceLabel }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <form action="{{ route('admin.gamification.points.destroy', $transaction) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
