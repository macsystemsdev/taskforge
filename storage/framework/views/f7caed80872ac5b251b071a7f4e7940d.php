<?php
use Livewire\Component;
use App\Models\Project;
use App\Actions\Projects\UpdateProjectAction;
use App\Data\Projects\UpdateProjectData;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
?>

<?php if (isset($component)) { $__componentOriginal1f4cdfbcf032dc00af93962c134fd24f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1f4cdfbcf032dc00af93962c134fd24f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page','data' => ['size' => '3xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => '3xl']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if (isset($component)) { $__componentOriginal91a231a9270579fa1ae9246bd51fb785 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91a231a9270579fa1ae9246bd51fb785 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['title' => __('Edit Project'),'description' => __('Update project information. Team ownership cannot be changed after creation.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Edit Project')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Update project information. Team ownership cannot be changed after creation.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $attributes = $__attributesOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__attributesOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91a231a9270579fa1ae9246bd51fb785)): ?>
<?php $component = $__componentOriginal91a231a9270579fa1ae9246bd51fb785; ?>
<?php unset($__componentOriginal91a231a9270579fa1ae9246bd51fb785); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


        <form wire:submit="updateProject" class="space-y-6">

            <div class="space-y-2">
                <label>Project Name</label>

                <input type="text" wire:model="name" class="w-full px-3 py-2.5">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm text-red-600">
                        <?php echo e($message); ?>

                    </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="space-y-2">
                <label>Team</label>

                <input type="text" value="<?php echo e($project->team->name); ?>" disabled
                    class="w-full px-3 py-2.5 bg-zinc-100 dark:bg-zinc-800">

                <p class="text-xs text-zinc-500">
                    Team ownership cannot be changed after project creation.
                </p>
            </div>

            <div class="space-y-2">
                <label>Description</label>

                <textarea wire:model="description" rows="5" class="w-full px-3 py-2.5"></textarea>
            </div>

            <div class="space-y-2">
                <label>Due Date</label>

                <input type="date" wire:model="dueDate" class="w-full px-3 py-2.5">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="tf-button-primary">
                    Save Changes
                </button>
            </div>

        </form>

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
<?php if (isset($__attributesOriginal1f4cdfbcf032dc00af93962c134fd24f)): ?>
<?php $attributes = $__attributesOriginal1f4cdfbcf032dc00af93962c134fd24f; ?>
<?php unset($__attributesOriginal1f4cdfbcf032dc00af93962c134fd24f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1f4cdfbcf032dc00af93962c134fd24f)): ?>
<?php $component = $__componentOriginal1f4cdfbcf032dc00af93962c134fd24f; ?>
<?php unset($__componentOriginal1f4cdfbcf032dc00af93962c134fd24f); ?>
<?php endif; ?><?php /**PATH D:\Code\taskforge\storage\framework\views/livewire/views/aa071b52.blade.php ENDPATH**/ ?>