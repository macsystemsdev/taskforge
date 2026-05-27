<?php

use App\Models\Project;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function render()
    {
        $this->project->load(['workspace', 'owner']);

        return view('livewire.projects.show-project');
    }
};
?>

<div class="max-w-5xl mx-auto py-10">

    <div class="flex items-center justify-between mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                <?php echo e($project->name); ?>

            </h1>

            <p class="text-zinc-500 mt-2">
                <?php echo e($project->description); ?>

            </p>
        </div>

        <div class="text-sm text-zinc-500">
            Workspace:
            <?php echo e($project->workspace->name); ?>

        </div>

    </div>

    <div class="grid grid-cols-3 gap-6">

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Status
            </p>

            <p class="mt-2 font-semibold">
                <?php echo e(ucfirst($project->status)); ?>

            </p>
        </div>

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Owner
            </p>

            <p class="mt-2 font-semibold">
                <?php echo e($project->owner->name); ?>

            </p>
        </div>

        <div class="p-6 rounded-2xl border bg-red">
            <p class="text-sm text-zinc-500">
                Due Date
            </p>

            <p class="mt-2 font-semibold">
                <?php echo e($project->due_date ?? 'No due date'); ?>

            </p>
        </div>

    </div>

    <div class="space-y-8 mt-4">

        <div class="rounded-2xl border bg-dark p-6">

            <h1 class="text-3xl font-bold">
                <?php echo e($project->name); ?>

            </h1>

            <p class="mt-3 text-zinc-600">
                <?php echo e($project->description); ?>

            </p>

        </div>

        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tasks.create-task', ['project' => $project]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1579313436-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('comments.comment-section', [
        'commentable' => $project,
    ]);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1579313436-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

</div>
<?php /**PATH D:\Code\taskforge\resources\views/livewire/projects/show-project.blade.php ENDPATH**/ ?>