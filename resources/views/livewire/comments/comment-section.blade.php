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
            'comments' => $this->commentable->comments()->with('user')->oldest()->get(),
        ]);
    }
};
?>

<x-ui.card class="space-y-6">
    <div>
        <h2 class="tf-panel-title">Comments</h2>
        <p class="tf-panel-subtitle">Collaborate around decisions, blockers, and follow-up context.</p>
    </div>

    {{-- COMMENT FORM --}}
    <form wire:submit="createComment" class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/[0.03]">

        <textarea wire:model="content" rows="4" class="w-full px-3 py-2.5" placeholder="Write a comment..."></textarea>

        @error('content')
            <p class="text-sm font-medium text-red-600 dark:text-red-400">
                {{ $message }}
            </p>
        @enderror

        <div class="flex justify-end">

            <button type="submit" class="tf-button-primary">
                Send Comment
            </button>

        </div>

    </form>

    {{-- COMMENTS --}}
    <div class="space-y-4">

        @forelse ($comments as $comment)
            <div class="flex gap-3">
                <x-ui.avatar :name="$comment->user->name" size="lg" />

                <div class="flex-1">

                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/60">

                        <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                            <h3 class="font-semibold text-zinc-950 dark:text-white">
                                {{ $comment->user->name }}
                            </h3>

                            <span class="text-xs text-zinc-500">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>

                        </div>

                        <p class="whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                            {{ $comment->content }}
                        </p>

                    </div>

                </div>

            </div>

        @empty
            <x-ui.empty-state title="No comments yet" description="Start the discussion by adding the first update or decision note." />
        @endforelse

    </div>

</x-ui.card>
