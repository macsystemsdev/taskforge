<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['id' => 'project-files-'.e($project->id).'','class' => 'space-y-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'project-files-'.e($project->id).'','class' => 'space-y-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="tf-panel-title">Project Files</h2>
            <p class="tf-panel-subtitle">Browse project attachments with search, sort, and filters.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(140px,200px)_minmax(90px,140px)] w-full sm:w-auto">
            <label for="project-files-search" class="sr-only">Search files</label>
            <input id="project-files-search" wire:model.debounce.500ms="search" type="search" placeholder="Search files..."
                aria-label="Search files"
                class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70" />

            <label for="project-files-sort" class="sr-only">Sort files</label>
            <select id="project-files-sort" wire:model="sort" wire:change="resetPage"
                aria-label="Sort files"
                class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
            </select>

            <label for="project-files-per-page" class="sr-only">Files per page</label>
            <select id="project-files-per-page" wire:model="perPage" wire:change="resetPage"
                aria-label="Files per page"
                class="w-full appearance-none rounded-2xl border border-zinc-200 bg-white px-4 py-2 pr-8 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
            </select>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-[1fr_140px]">
        <div>
            <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300">File type</label>
            <select wire:model="type" wire:change="resetPage" class="mt-2 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="all">All files</option>
                <option value="images">Images</option>
                <option value="documents">Documents</option>
                <option value="spreadsheets">Spreadsheets</option>
                <option value="others">Other files</option>
            </select>
        </div>

        <div class="flex items-end justify-end text-sm text-zinc-500" aria-live="polite">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachments !== null): ?>
                <span><?php echo e($attachments instanceof \Illuminate\Contracts\Pagination\Paginator ? $attachments->total() : count($attachments)); ?> files</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $ready): ?>
        <div class="space-y-3">
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
        </div>
    <?php elseif($attachments && $attachments->isNotEmpty()): ?>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $file = $attachment->storedFile;
                ?>

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80 hover:shadow-md transition">
                    <div class="grid grid-cols-[56px_1fr_auto] items-center gap-4">
                        <div class="h-14 w-14 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-900/40">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file && str_starts_with($file->mime_type, 'image/') && isset($attachment->preview_url)): ?>
                                <img src="<?php echo e($attachment->preview_url); ?>" alt="<?php echo e($file->original_filename); ?>" class="h-14 w-14 object-cover rounded" />
                            <?php else: ?>
                                <div class="text-2xl">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(str_starts_with($file->mime_type, 'image/')): ?>
                                            🖼️
                                        <?php elseif($file->mime_type === 'application/pdf'): ?>
                                            📄
                                        <?php elseif(str_contains($file->mime_type, 'spreadsheet')): ?>
                                            📊
                                        <?php else: ?>
                                            📎
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        📎
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-zinc-950 dark:text-white text-sm"><?php echo e($file->original_filename); ?></p>
                            <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                <span><?php echo e($attachment->uploader->name); ?></span>
                                <span aria-hidden="true">•</span>
                                <span><?php echo e($attachment->created_at->diffForHumans()); ?></span>
                                <span aria-hidden="true">•</span>
                                <span><?php echo e($file ? $file->extension : ''); ?> · <?php echo e($file ? number_format($file->size / 1024, 1) . ' KB' : ''); ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                                    <a href="<?php echo e(route('projects.attachments.view', [$project, $attachment])); ?>" target="_blank" class="inline-flex items-center gap-2 rounded-md border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-950/80 dark:text-zinc-100 dark:hover:bg-zinc-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 10a8 8 0 1116 0A8 8 0 012 10zm9-3a1 1 0 10-2 0v3a1 1 0 00.293.707l2 2a1 1 0 001.414-1.414L11 9.586V7z"/></svg>
                                <span>Preview</span>
                            </a>

                            <a href="<?php echo e(route('projects.attachments.download', [$project, $attachment])); ?>" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3a1 1 0 000 2h14a1 1 0 100-2H3zM7 8a1 1 0 012 0v5h2V8a1 1 0 112 0v5a1 1 0 01-1 1H8a1 1 0 01-1-1V8z"/></svg>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachments instanceof \Illuminate\Contracts\Pagination\Paginator): ?>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-zinc-500">
                    Showing <?php echo e($attachments->firstItem()); ?>–<?php echo e($attachments->lastItem()); ?> of <?php echo e($attachments->total()); ?>

                </div>

                <div class=""> 
                    <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <?php echo e($attachments->links()); ?>

                    </nav>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['title' => 'No project files','description' => 'Upload attachments from the discussion or use the file library once files exist.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'No project files','description' => 'Upload attachments from the discussion or use the file library once files exist.']); ?>
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
    (function () {
        function payload(e) { return Array.isArray(e.detail) ? e.detail[0] : e.detail; }

        function refresh() {
            var el = document.getElementById('project-files-<?php echo e($project->id); ?>');
            if (!el) return;
            var id = el.getAttribute('wire:id');
            if (!id || !window.Livewire) return;
            try {
                var comp = window.Livewire.find(id);
                if (comp && typeof comp.call === 'function') {
                    // Ensure the component reloads attachments
                    comp.call('loadAttachments');
                }
            } catch (err) {
                // ignore
            }
        }

        window.addEventListener('attachmentsUploaded', function (e) { refresh(); });
        window.addEventListener('commentCreated', function (e) { refresh(); });
    })();
</script>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/projects/project-files.blade.php ENDPATH**/ ?>