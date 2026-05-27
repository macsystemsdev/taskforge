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

    
    <form wire:submit="createComment" class="space-y-4 mb-8">

        <textarea wire:model="content" rows="4" class="w-full rounded-2xl border px-4 py-3" placeholder="Write a comment..."></textarea>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-sm text-red-500">
                <?php echo e($message); ?>

            </p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="flex justify-end">

            <button type="submit" class="rounded-xl bg-black px-5 py-3 text-white">
                Send Comment
            </button>

        </div>

    </form>

    
    <div class="space-y-6">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex gap-4">

                <div class="h-10 w-10 rounded-full bg-zinc-200 flex items-center justify-center text-sm font-semibold">

                    <?php echo e(strtoupper(substr($comment->user->name, 0, 1))); ?>


                </div>

                <div class="flex-1">

                    <div class="rounded-2xl border p-4">

                        <div class="flex items-center justify-between mb-2">

                            <h3 class="font-semibold">
                                <?php echo e($comment->user->name); ?>

                            </h3>

                            <span class="text-xs text-zinc-500">
                                <?php echo e($comment->created_at->diffForHumans()); ?>

                            </span>

                        </div>

                        <p class="text-zinc-700 whitespace-pre-line">
                            <?php echo e($comment->content); ?>

                        </p>

                    </div>

                </div>

            </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <div class="rounded-2xl border border-dashed p-8 text-center">

                <p class="text-zinc-500">
                    No comments yet.
                </p>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/comments/comment-section.blade.php ENDPATH**/ ?>