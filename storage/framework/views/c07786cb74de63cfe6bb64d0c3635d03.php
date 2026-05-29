<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public Workspace $workspace;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    public function createProject(CreateProjectAction $action)
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Project::where('slug', Str::slug((string) $value))->exists()) {
                        $fail(__('A project with that name already exists.'));
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        // pass data into Project DTO for binding into action
        $data = new CreateProjectData(owner_id: auth()->id(), name: $validated['name'], description: $validated['description'], due_date: $validated['due_date']);

        // handle function call in CreateprojectAction to create project with DTO data
        $project = $action->handle(workspace: $this->workspace, data: $data);

        Flux::toast(variant: 'success', text: __('Project created successfully.'));

        return redirect()->route('projects.show', $project);
    }

    // render this page which will pick up layout from the pages/projects
    public function render()
    {
        return view('livewire.projects.create-project');
    }
};

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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['title' => __('Create Project'),'description' => __('Create a project inside :workspace and define its first operational boundary.', ['workspace' => $workspace->name])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create Project')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create a project inside :workspace and define its first operational boundary.', ['workspace' => $workspace->name]))]); ?>
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

        <form wire:submit="createProject" class="space-y-6">
            <div class="space-y-2">
                <label for="project-name">Project Name</label>
                <input id="project-name" type="text" wire:model="name" class="w-full px-3 py-2.5">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">
                        <?php echo e($message); ?>

                    </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="space-y-2">
                <label for="project-description">Description</label>
                <textarea id="project-description" wire:model="description" rows="5" class="w-full px-3 py-2.5"></textarea>
            </div>

            <div class="space-y-2">
                <label for="project-due-date">Due Date</label>
                <input id="project-due-date" type="date" wire:model="due_date" class="w-full px-3 py-2.5">
            </div>

            <div class="flex justify-end border-t border-zinc-200 pt-5 dark:border-white/10">
                <button type="submit" class="tf-button-primary">
                    Create Project
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
<?php endif; ?>
<?php /**PATH D:\Code\taskforge\resources\views\livewire\projects\create-project.blade.php ENDPATH**/ ?>