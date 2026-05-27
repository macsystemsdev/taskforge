<?php

use App\Actions\Comments\CreateCommentAction;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

new class extends Component {
    
    public Model $commentable;

    public string $content = '';

    public function createComment(CreateCommentAction $action): void
    {
        $validated = $this->validate([
            'content' => ['required', 'string'],
        ]);

        $action->handle(commentable: $this->commentable, content: $validated['content']);

        $this->reset('content');
    }

    public function render()
    {
        return view('livewire.comments.comment-section', [
            'comments' => $this->commentable->comments()->with('user')->latest()->get(),
        ]);
    }
};
?>

<div class="rounded-2xl mt-5 border bg-dark p-6 shadow-sm">

    <h2 class="text-xl font-semibold mb-6">
        Comments
    </h2>

    {{-- COMMENT FORM --}}
    <form wire:submit="createComment" class="space-y-4 mb-8">

        <textarea wire:model="content" rows="4" class="w-full rounded-2xl border px-4 py-3" placeholder="Write a comment..."></textarea>

        @error('content')
            <p class="text-sm text-red-500">
                {{ $message }}
            </p>
        @enderror

        <div class="flex justify-end">

            <button type="submit" class="rounded-xl bg-black px-5 py-3 text-white">
                Send Comment
            </button>

        </div>

    </form>

    {{-- COMMENTS --}}
    <div class="space-y-6">

        @forelse ($comments as $comment)
            <div class="flex gap-4">

                <div class="h-10 w-10 rounded-full bg-zinc-200 flex items-center justify-center text-sm font-semibold">

                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}

                </div>

                <div class="flex-1">

                    <div class="rounded-2xl border p-4">

                        <div class="flex items-center justify-between mb-2">

                            <h3 class="font-semibold">
                                {{ $comment->user->name }}
                            </h3>

                            <span class="text-xs text-zinc-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>

                        </div>

                        <p class="text-zinc-700 whitespace-pre-line">
                            {{ $comment->content }}
                        </p>

                    </div>

                </div>

            </div>

        @empty

            <div class="rounded-2xl border border-dashed p-8 text-center">

                <p class="text-zinc-500">
                    No comments yet.
                </p>

            </div>
        @endforelse

    </div>

</div>
