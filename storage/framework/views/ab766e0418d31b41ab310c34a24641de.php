<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Project;
use App\Models\Workspace;
use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Workspace $workspace;

    public string $name = '';

    public ?string $description = null;

    public ?string $dueDate = null;

    public ?int $teamId = null;

     public function getTeamsProperty()
    {
        return $this->workspace
            ->teams()
            ->orderBy('name')
            ->get();
    }

    public function createProject(CreateProjectAction $action)
    {
        Gate::authorize('createProject', $this->workspace);

        $validated = $this->validate();

        // handle function call in CreateprojectAction to create project with DTO data
         $project = $action->handle(
            $this->workspace,
            new CreateProjectData(
                teamId: $this->teamId,
                name: $this->name,
                description: $this->description,
                dueDate: $this->dueDate,
            )
        );

        Flux::toast(variant: 'success', text: __('Project created successfully.'));

          return redirect()->route(
            'projects.show',
            [
                'workspace' => $this->workspace,
                'project' => $project,
            ]
        );
    }

        public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
            ],

            'teamId' => [
                'required',
                'exists:teams,id',
            ],

            'description' => [
                'nullable',
            ],

            'dueDate' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.page-header','data' => ['title' => __('Create Project'),'description' => __('Create a project inside :workspace and define its first operational boundary.', [
        'workspace' => $workspace->name,
    ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create Project')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create a project inside :workspace and define its first operational boundary.', [
        'workspace' => $workspace->name,
    ]))]); ?>
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


        <form
            wire:submit="createProject"
            class="space-y-6"
        >

            <div class="space-y-2">
                <label>Project Name</label>

                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-3 py-2.5"
                >

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

                <select
                    wire:model="teamId"
                    class="w-full px-3 py-2.5"
                >
                    <option value="">
                        Select team
                    </option>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($team->id); ?>">
                            <?php echo e($team->name); ?>

                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['teamId'];
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
                <label>Description</label>

                <textarea
                    wire:model="description"
                    rows="5"
                    class="w-full px-3 py-2.5"
                ></textarea>
            </div>

            <div class="space-y-2">
                <label>Due Date</label>

                <input
                    type="date"
                    wire:model="dueDate"
                    class="w-full px-3 py-2.5"
                >
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="tf-button-primary"
                >
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
<?php /**PATH D:\Code\taskforge\resources\views/livewire/projects/create-project.blade.php ENDPATH**/ ?>