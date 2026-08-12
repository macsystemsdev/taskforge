<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['wire:init' => 'loadAttachments','class' => 'space-y-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:init' => 'loadAttachments','class' => 'space-y-6']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div>
        <h2 class="tf-panel-title">Comments</h2>
        <p class="tf-panel-subtitle">Collaborate around decisions, blockers, and follow-up context.</p>
    </div>

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'flex min-h-[560px] flex-col overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50/80 shadow-sm transition dark:border-white/10 dark:bg-white/[0.03]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex min-h-[560px] flex-col overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50/80 shadow-sm transition dark:border-white/10 dark:bg-white/[0.03]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-zinc-950 dark:text-white">Discussion</h3>
                    <p class="text-sm text-zinc-500">Recent project comments and context.</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-zinc-500" aria-live="polite">
                    <span>Showing <?php echo e($comments->count()); ?> of <?php echo e($commentsTotal); ?> comments</span>
                    <button id="jump-to-latest-<?php echo e($commentable->id); ?>" type="button"
                        class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs font-semibold text-zinc-600 shadow-sm transition hover:border-blue-300 hover:text-blue-700 dark:border-white/10 dark:bg-zinc-950/80 dark:text-zinc-300">Latest</button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden px-5 py-4">
            <div id="comments-list-<?php echo e($commentable->id); ?>"
                class="flex h-full max-h-[420px] min-h-[280px] flex-col gap-4 overflow-y-auto pr-2"
                aria-label="Recent comments">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($commentsTotal > $comments->count()): ?>
                    <div class="flex justify-center">
                        <button type="button" wire:click="loadMoreComments"
                            class="tf-button-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300">
                            Load more comments
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $isMe = optional(auth()->user())->id === $comment->user->id; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMe): ?>
                        <div id="comment-<?php echo e($comment->id); ?>" class="flex gap-3 justify-end px-2">
                            <div class="max-w-[70%] text-right">
                                <div class="text-xs text-zinc-400 mb-2">You ·
                                    <?php echo e($comment->created_at->diffForHumans()); ?></div>
                                <div
                                    class="inline-block rounded-3xl bg-blue-600 text-white chat-bubble shadow-sm transition-all duration-200">
                                    <p class="whitespace-pre-line text-sm leading-7"><?php echo e($comment->content); ?></p>
                                </div>
                            </div>

                            <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $comment->user->name,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($comment->user->name),'size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div id="comment-<?php echo e($comment->id); ?>" class="flex gap-3 px-2">
                            <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $comment->user->name,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($comment->user->name),'size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>

                            <div class="max-w-[70%]">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="font-semibold text-zinc-950 dark:text-white">
                                            <?php echo e($comment->user->name); ?></h3>
                                        <p class="text-xs text-zinc-500"><?php echo e($comment->created_at->diffForHumans()); ?></p>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 inline-block rounded-3xl border border-zinc-200 bg-white chat-bubble shadow-sm dark:border-white/10 dark:bg-zinc-950/60">
                                    <p class="whitespace-pre-line text-sm leading-7 text-zinc-700 dark:text-zinc-300">
                                        <?php echo e($comment->content); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['title' => 'No comments yet','description' => 'Start the discussion by adding the first update or decision note.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No comments yet','description' => 'Start the discussion by adding the first update or decision note.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        </div>

        <div class="border-t border-zinc-200 bg-white px-5 py-4 dark:border-white/10 dark:bg-zinc-950/95">
            <form wire:submit.prevent="createComment" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-[auto_1fr_auto] md:items-end">
                    <label for="uploads-<?php echo e($commentable->id); ?>"
                        class="group flex h-12 w-12 cursor-pointer items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 text-zinc-500 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-white/10 dark:bg-zinc-950/60 dark:text-zinc-300 dark:hover:bg-blue-950/40">
                        <span class="text-xl">📎</span>
                        <input id="uploads-<?php echo e($commentable->id); ?>" wire:model="uploads" type="file" multiple
                            class="sr-only" />
                    </label>

                    <div class="relative">
                        <textarea wire:model="content" rows="3"
                            class="w-full resize-none rounded-3xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-14 text-sm leading-6 text-zinc-900 transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-white/10 dark:bg-zinc-950/80 dark:text-white"
                            placeholder="Type a message"></textarea>
                        <button type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Send
                        </button>
                    </div>

                    <button type="button" wire:click="uploadAttachments" wire:loading.attr="disabled"
                        class="tf-button-secondary hidden h-12 px-4 py-0 text-sm md:inline-flex">
                        Upload
                    </button>
                </div>

                <div id="drop-zone-<?php echo e($commentable->id); ?>"
                    class="rounded-3xl border-2 border-dashed border-zinc-200 bg-white/80 px-4 py-3 text-sm text-zinc-500 transition hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:text-zinc-400">
                    Drag and drop media here, or tap the paperclip to attach files. Maximum size is 10MB per file.
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($uploads): ?>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $uploads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-sm text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                <?php echo e($file->getClientOriginalName()); ?>

                                <span class="text-xs text-blue-500 dark:text-blue-400">
                                    <?php echo e(number_format($file->getSize() / 1024, 1)); ?> KB
                                </span>
                                <button type="button" wire:click="removeUpload(<?php echo e($index); ?>)"
                                    class="hover:text-blue-900 dark:hover:text-blue-100">
                                    ×
                                </button>
                            </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm font-medium text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploads'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm font-medium text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploads.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm font-medium text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span wire:loading wire:target="uploadAttachments" role="status" aria-live="polite">Uploading
                        attachments…</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($uploadSuccess): ?>
                        <span
                            class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-300">Files
                            uploaded successfully.</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </form>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'space-y-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-zinc-950 dark:text-white">Discussion attachments</h3>
                <p class="text-sm text-zinc-500">Files added directly to this project discussion.</p>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$ready): ?>
            <div class="space-y-3">
                <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
                <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
                <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
            </div>
        <?php elseif($attachments === null || $attachments->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['title' => 'No attachments yet','description' => 'Upload files to keep the discussion grounded in context.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No attachments yet','description' => 'Upload files to keep the discussion grounded in context.']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="max-h-[420px] overflow-y-auto space-y-3 pr-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php $file = $attachment->storedFile; ?>
                    <div
                        class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                        <div class="flex items-start gap-3">
                            <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-zinc-100 text-2xl">
                                <?php echo e($this->attachmentIcon($file->mime_type)); ?>

                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">
                                            <?php echo e($file->original_filename); ?></p>
                                        <p class="text-sm text-zinc-500"><?php echo e($attachment->uploader->name); ?> ·
                                            <?php echo e($attachment->created_at->diffForHumans()); ?></p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="<?php echo e(route('projects.attachments.download', [$commentable, $attachment])); ?>"
                                            class="tf-button-secondary inline-flex items-center justify-center gap-2">
                                            Download
                                        </a>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($file->mime_type, 'image/') || $file->mime_type === 'application/pdf'): ?>
                                            <a href="<?php echo e(route('projects.attachments.view', [$commentable, $attachment])); ?>"
                                                target="_blank"
                                                class="tf-button-secondary inline-flex items-center justify-center gap-2">
                                                Preview
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $commentable)): ?>
                                            <button type="button" wire:click="deleteAttachment(<?php echo e($attachment->id); ?>)"
                                                wire:loading.attr="disabled"
                                                class="tf-button-danger inline-flex items-center justify-center gap-2">
                                                Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="mt-3 text-sm text-zinc-500"><?php echo e($this->formatBytes($file->size)); ?></p>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($file->mime_type, 'image/') && isset($attachment->preview_url)): ?>
                                    <div
                                        class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-50 dark:border-white/10">
                                        <img src="<?php echo e($attachment->preview_url); ?>"
                                            alt="<?php echo e($file->original_filename); ?>" class="h-32 w-full object-cover" />
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachmentsTotal && $attachmentsTotal > $attachments->count()): ?>
                <div class="mt-4 flex justify-center">
                    <button wire:click="loadMoreAttachments" class="tf-button-secondary">Load more</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<script>
    (function() {
        var commentsList = document.getElementById('comments-list-<?php echo e($commentable->id); ?>');
        var scrollToBottom = function() {
            if (commentsList) {
                commentsList.scrollTop = commentsList.scrollHeight;
            }
        };

        document.addEventListener('livewire:load', scrollToBottom);
        window.addEventListener('load', scrollToBottom);

        var jumpButton = document.getElementById('jump-to-latest-<?php echo e($commentable->id); ?>');
        if (jumpButton) {
            jumpButton.addEventListener('click', scrollToBottom);
        }

        function getPayload(e) {
            return Array.isArray(e.detail) ? e.detail[0] : e.detail;
        }

        window.addEventListener('attachmentsUploaded', function(e) {
            var payload = getPayload(e) || {};
            var count = payload.count ?? 0;
            var message = count + ' file' + (count === 1 ? '' : 's') + ' uploaded';

            // Prefer the app's flux toast if available
            try {
                if (window.flux && typeof window.flux.toast === 'function') {
                    window.flux.toast({
                        message: message,
                        type: 'success',
                    });
                    return;
                }
            } catch (err) {
                // ignore and fall back
            }

            // Fallback: simple transient toast appended to body
            var toast = document.createElement('div');
            toast.textContent = message;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 9999;
            toast.style.background = '#059669';
            toast.style.color = 'white';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.1)';
            toast.style.fontFamily =
                'Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
            toast.style.fontSize = '13px';

            document.body.appendChild(toast);

            setTimeout(function() {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
            }, 2200);

            setTimeout(function() {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 2600);
        });
    })();
</script>

<script>
    (function() {
        window.addEventListener('attachmentsUploadFailed', function(e) {
            var payload = Array.isArray(e.detail) ? e.detail[0] : e.detail;
            var message = payload?.message ?? 'Upload failed';

            try {
                if (window.flux && typeof window.flux.toast === 'function') {
                    window.flux.toast({
                        message: message,
                        type: 'error',
                    });
                    return;
                }
            } catch (err) {
                // ignore
            }

            var toast = document.createElement('div');
            toast.textContent = message;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 9999;
            toast.style.background = '#dc2626';
            toast.style.color = 'white';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.1)';
            toast.style.fontFamily =
                'Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
            toast.style.fontSize = '13px';

            document.body.appendChild(toast);

            setTimeout(function() {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
            }, 3200);

            setTimeout(function() {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 3600);
        });
    })();
</script>

<style>
    /* Highlight animation for newly created comments (color pulse) */
    .new-comment {
        animation: highlightComment 2.2s ease forwards;
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.08);
        transform-origin: top left;
    }

    @keyframes highlightComment {
        0% {
            background-color: rgba(59, 130, 246, 0.18);
            transform: translateY(-6px) scale(1.02);
            opacity: 0.98;
        }

        30% {
            background-color: rgba(59, 130, 246, 0.12);
            transform: translateY(0) scale(1.005);
            opacity: 1;
        }

        70% {
            background-color: rgba(59, 130, 246, 0.06);
            transform: translateY(0) scale(1);
        }

        100% {
            background-color: transparent;
            transform: none;
            opacity: 1;
        }
    }

    /* Entrance animation for new comments (slide + fade) */
    .enter-comment {
        animation: enterComment 360ms cubic-bezier(.2, .9, .2, 1) forwards;
    }

    @keyframes enterComment {
        0% {
            transform: translateY(8px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Responsive bubble widths and padding */
    .chat-bubble {
        max-width: 85%;
        padding: 0.6rem;
    }

    @media (min-width: 640px) {
        .chat-bubble {
            max-width: 75%;
            padding: 0.75rem;
        }
    }

    @media (min-width: 768px) {
        .chat-bubble {
            max-width: 70%;
            padding: 1rem;
        }
    }

    .drop-zone-active {
        border-color: #2563eb;
        background-color: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
    }
</style>

<script>
    (function() {
        var dropZone = document.getElementById('drop-zone-<?php echo e($commentable->id); ?>');
        var fileInput = document.getElementById('uploads-<?php echo e($commentable->id); ?>');

        var preventDefault = function(event) {
            event.preventDefault();
            event.stopPropagation();
        };

        window.addEventListener('dragover', preventDefault);
        window.addEventListener('drop', preventDefault);

        if (dropZone && fileInput) {
            var setActive = function(active) {
                dropZone.classList.toggle('drop-zone-active', active);
            };

            dropZone.addEventListener('dragover', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(true);
            });

            dropZone.addEventListener('dragleave', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(false);
            });

            dropZone.addEventListener('drop', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(false);

                if (event.dataTransfer && event.dataTransfer.files.length) {
                    try {
                        fileInput.files = event.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } catch (err) {
                        console.warn('Unable to assign dropped files to input.', err);
                    }
                }
            });
        }
    })();

    (function() {
        function getPayload(e) {
            return Array.isArray(e.detail) ? e.detail[0] : e.detail;
        }

        window.addEventListener('commentCreated', function(e) {
            var payload = getPayload(e) || {};
            var id = payload.id;

            // scroll to bottom (chat-like) then highlight the new comment after DOM updates
            try {
                var container = document.getElementById('comments-list-<?php echo e($commentable->id); ?>');
                if (container) container.scrollTop = container.scrollHeight;
            } catch (err) {}

            if (!id) return;

            // wait briefly so Livewire has a chance to re-render the new comment node
            setTimeout(function() {
                try {
                    var el = document.getElementById('comment-' + id);
                    if (el) {
                        el.classList.add('enter-comment', 'new-comment');
                        setTimeout(function() {
                            el.classList.remove('new-comment');
                        }, 2400);
                        setTimeout(function() {
                            el.classList.remove('enter-comment');
                        }, 500);
                    }
                } catch (err) {
                    // ignore
                }
            }, 120);
        });
    })();
</script>
<?php /**PATH /var/www/html/resources/views/livewire/comments/comment-section.blade.php ENDPATH**/ ?>