@php
    $userLiked = $comment->likes->contains('user_id', auth()->id());
@endphp
<div class="pc-comment" id="comment-{{ $comment->id }}">
    <div class="pc-comment__avatar">{{ mb_substr($comment->user->name ?? 'U', 0, 1) }}</div>
    <div class="pc-comment__body">
        <div class="pc-comment__header">
            <span class="pc-comment__author">{{ $comment->user->name ?? $comment->user->email }}</span>
            <span class="pc-comment__time">{{ $comment->created_at?->diffForHumans() }}</span>
        </div>
        <div class="pc-comment__text">{{ $comment->body }}</div>
        <div class="pc-comment__actions">
            <form action="{{ route('student.community-projects.comments.like', $comment->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="pc-like-btn @if($userLiked) pc-like-btn--liked @endif">
                    <i class="fe fe-heart"></i>
                    {{ $comment->likes->count() }}
                </button>
            </form>
            @if($depth < 2)
                <button type="button" class="pc-like-btn reply-toggle-btn" data-target="reply-form-{{ $comment->id }}">
                    <i class="fe fe-message-circle"></i> رد
                </button>
            @endif
        </div>

        @if($depth < 2)
            <form action="{{ route('student.community-projects.comments.store', $showcase->slug) }}" method="POST"
                  class="mt-2 reply-form" id="reply-form-{{ $comment->id }}" style="display:none">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="body" class="form-control form-control-sm mb-2" rows="2" placeholder="اكتب ردك..." required maxlength="5000"></textarea>
                <button type="submit" class="btn btn-sm btn-primary">إرسال الرد</button>
            </form>
        @endif

        @if($comment->replies->isNotEmpty())
            <div class="pc-comment-replies">
                @foreach($comment->replies as $reply)
                    @include('student.pages.community-projects._comment', ['comment' => $reply, 'showcase' => $showcase, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.reply-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById(btn.dataset.target);
            if (form) form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    });
});
</script>
@endpush
@endonce
