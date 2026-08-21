<?php

use App\Models\Project;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function render()
    {
        return view('livewire.projects.project-details');
    }
};

?>

<?php if (isset($component)) { $__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'e60dd9d2c3a62d619c9acb38f20d5aa5::card.index','data' => ['class' => '!p-0 overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('flux::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0 overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Project Details</h2>
    </div>

    <div class="space-y-4 p-6">
        
        <div
            class="rounded-xl border p-4 
            <?php if($project->hasOverdueTasks()): ?> border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/40
            <?php elseif($project->hasUpcomingDeadlines()): ?>
                border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/40
            <?php else: ?>
                border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/40 <?php endif; ?>">
            <p class="tf-muted text-xs">Health</p>
            <p
                class="mt-1 font-semibold 
                <?php if($project->hasOverdueTasks()): ?> text-red-600 dark:text-red-400
                <?php elseif($project->hasUpcomingDeadlines()): ?>
                    text-amber-600 dark:text-amber-400
                <?php else: ?>
                    text-emerald-600 dark:text-emerald-400 <?php endif; ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->hasOverdueTasks()): ?>
                    At Risk
                <?php elseif($project->hasUpcomingDeadlines()): ?>
                    Attention Needed
                <?php else: ?>
                    Healthy
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>

        
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Overdue</dt>
                <dd class="mt-1 text-lg font-semibold text-red-600 dark:text-red-400"><?php echo e($project->overdueTaskCount()); ?>

                </dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Due Soon</dt>
                <dd class="mt-1 text-lg font-semibold text-amber-600 dark:text-amber-400">
                    <?php echo e($project->dueSoonTaskCount()); ?></dd>
            </div>
        </div>

        
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Team</dt>
                <dd class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white"><?php echo e($project->team->name); ?>

                </dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created By</dt>
                <dd class="mt-1 truncate text-sm font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($project->creator->name); ?></dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Status</dt>
                <dd class="mt-1"><?php if (isset($component)) { $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.status-badge','data' => ['status' => $project->status,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->status),'size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $attributes = $__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__attributesOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8)): ?>
<?php $component = $__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8; ?>
<?php unset($__componentOriginaldf5a194c1ccdd1698e9a89f0cb5bf2c8); ?>
<?php endif; ?></dd>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Created</dt>
                <dd class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($project->created_at->format('M d, Y')); ?></dd>
            </div>
            <div
                class="col-span-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/80">
                <dt class="tf-muted text-xs">Due Date</dt>
                <dd class="mt-1 text-sm font-semibold text-zinc-950 dark:text-white">
                    <?php echo e($project->due_date?->format('M d, Y') ?? __('No due date')); ?></dd>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec)): ?>
<?php $attributes = $__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec; ?>
<?php unset($__attributesOriginalc4bce27d2c09d2f98a63d67977c1c3ec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec)): ?>
<?php $component = $__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec; ?>
<?php unset($__componentOriginalc4bce27d2c09d2f98a63d67977c1c3ec); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/projects/project-details.blade.php ENDPATH**/ ?>