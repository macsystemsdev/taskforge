<?php

namespace App\Livewire\Tasks;

use App\Actions\Tasks\CreateTaskAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Flux\Flux;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public ?string $statusFilter = null;

    public string $title = '';

    public string $description = '';

    public ?int $assigned_to = null;

    public string $priority = 'medium';

    public ?string $due_date = null;

    public function createTask(CreateTaskAction $action)
    {
        $tasks = $this->project->tasks()->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))->latest()->get();

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'assigned_to' => ['nullable', 'exists:users,id'],

            'priority' => ['required', 'in:low,medium,high,urgent'],

            'due_date' => ['nullable', 'date'],
        ]);

        $data = new CreateTaskData(title: $validated['title'], description: $validated['description'] ?? null, assigned_to: $validated['assigned_to'] ?? null, priority: $validated['priority'], due_date: $validated['due_date'] ?? null);

        $action->handle(project: $this->project, data: $data);

        $this->reset(['title', 'description', 'assigned_to', 'priority', 'due_date']);

        Flux::toast(variant: 'success', text: __('Task created successfully.'));

        $this->dispatch('task-created');
    }

    public function render()
    {
        return view('livewire.tasks.create-task', [
            'members' => $this->project->workspace->organization->members,
        ]);
    }
};
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    
    <div class="lg:col-span-1">

        <div class="rounded-2xl border bg-dark p-6 shadow-sm">

            <h2 class="text-xl font-semibold mb-6">
                Create Task
            </h2>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-4 rounded-xl bg-green-100 px-4 py-3 text-green-700">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form wire:submit="createTask" class="space-y-5">

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Title
                    </label>

                    <input type="text" wire:model="title" class="w-full rounded-xl border px-4 py-3">

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-sm text-red-500 mt-1">
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

                    <textarea wire:model="description" rows="4" class="w-full rounded-xl border px-4 py-3"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Assign To
                    </label>

                    <select wire:model="assigned_to" class="w-full rounded-xl border px-4 py-3">
                        <option value="">
                            Unassigned
                        </option>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($member->id); ?>">
                                <?php echo e($member->name); ?>

                            </option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Priority
                    </label>

                    <select wire:model="priority" class="w-full rounded-xl border px-4 py-3">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        Due Date
                    </label>

                    <input type="date" wire:model="due_date" class="w-full rounded-xl border px-4 py-3">
                </div>

                <button type="submit" class="w-full rounded-xl bg-black px-5 py-3 text-white">
                    Create Task
                </button>

            </form>

        </div>

    </div>

    
    <div class="lg:col-span-2">

        <div class="rounded-2xl border bg-dark p-6 shadow-sm">

            <div class="flex items-center justify-between mb-6">

                <h2 class="text-xl font-semibold">
                    Project Tasks
                </h2>

                <span class="text-sm text-zinc-500">
                    <?php echo e($project->tasks->count()); ?> Tasks
                </span>

            </div>

            <div class="space-y-4">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $project->tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="rounded-2xl border p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <h3 class="font-semibold text-lg">
                                    <a href="<?php echo e(route('tasks.show', $task)); ?>" class="font-semibold hover:underline">
                                        <?php echo e($task->title); ?>

                                    </a>
                                </h3>

                                <p class="text-sm text-zinc-500 mt-2">
                                    <?php echo e($task->description); ?>

                                </p>

                                <div class="mt-4 flex items-center gap-3 text-sm text-zinc-500">

                                    <span>
                                        Assigned to:
                                        <?php echo e($task->assignee?->name ?? 'Unassigned'); ?>

                                    </span>

                                    <span>
                                        Due:
                                        <?php echo e($task->due_date?->format('M d, Y') ?? 'N/A'); ?>

                                    </span>

                                </div>

                            </div>

                            <div class="flex flex-col gap-2 items-end">

                                <span class="rounded-full border px-3 py-1 text-sm">
                                    <?php echo e(str($task->priority->value)->headline()); ?>

                                </span>

                                <span class="rounded-full border px-3 py-1 text-sm">
                                    <?php echo e(str($task->status->value)->headline()); ?>

                                </span>

                            </div>

                        </div>

                    </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <div class="rounded-2xl border border-dashed p-10 text-center">

                        <p class="text-zinc-500">
                            No tasks created yet.
                        </p>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>

        </div>

    </div>

</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/tasks/create-task.blade.php ENDPATH**/ ?>