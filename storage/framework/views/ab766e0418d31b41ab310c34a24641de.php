<?php

use App\Actions\Projects\CreateProjectAction;
use App\Data\Projects\CreateProjectData;
use App\Models\Workspace;
use Livewire\Component;

new class extends Component {
    public Workspace $workspace;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    public function createProject(CreateProjectAction $action)
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        // pass data into Project DTO for binding into action
        $data = new CreateProjectData(owner_id: auth()->id(), name: $validated['name'], description: $validated['description'], due_date: $validated['due_date']);

        // handle function call in CreateprojectAction to create project with DTO data
        $project = $action->handle(workspace: $this->workspace, data: $data);

        return redirect()->route('projects.show', $project);
    }

    // render this page which will pick up layout from the pages/projects
    public function render()
    {
        return view('livewire.projects.create-project');
    }
};

?>
<div>
    <div class="max-w-3xl mx-auto py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold">
                Create Project
            </h1>

            <p class="text-zinc-500 mt-2">
                Create a new project inside <?php echo e($workspace->name); ?>.
            </p>
        </div>

        <form wire:submit="createProject" class="space-y-6">

            <div>
                <label class="block text-sm font-medium mb-2">
                    Project Name
                </label>

                <input type="text" wire:model="name" class="w-full rounded-xl border-zinc-300">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-red-500 text-sm mt-1">
                        <?php echo e($message); ?>

                    </p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    Description
                </label>

                <textarea wire:model="description" rows="5" class="w-full rounded-xl border-zinc-300"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    Due Date
                </label>

                <input type="date" wire:model="due_date" class="w-full rounded-xl border-zinc-300">
            </div>

            <div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-black text-white">
                    Create Project
                </button>
            </div>

        </form>
    </div>
</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/projects/create-project.blade.php ENDPATH**/ ?>