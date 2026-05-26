<?php

namespace App\Livewire\Tasks;

use App\Actions\Tasks\CreateTaskAction;
use App\Data\Tasks\CreateTaskData;
use App\Models\Project;
use Livewire\Component;

class CreateTask extends Component
{
    public Project $project;

    public string $title = '';

    public string $description = '';

    public ?int $assigned_to = null;

    public string $priority = 'medium';

    public ?string $due_date = null;

    public function createTask(CreateTaskAction $action)
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'assigned_to' => ['nullable', 'exists:users,id'],

            'priority' => ['required', 'in:low,medium,high,urgent'],

            'due_date' => ['nullable', 'date'],
        ]);

        $data = new CreateTaskData(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            assigned_to: $validated['assigned_to'] ?? null,
            priority: $validated['priority'],
            due_date: $validated['due_date'] ?? null,
            
        );

        $action->handle(project: $this->project, data: $data);

        $this->reset(['title', 'description', 'assigned_to', 'priority', 'due_date']);

        session()->flash('success', 'Task created successfully.');

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

<div class="rounded-2xl border bg-white p-6">

    <h2 class="text-xl font-semibold mb-6">
        Create Task
    </h2>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-green-100 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="createTask" class="space-y-5">

        <div>
            <label class="block text-sm font-medium mb-2">
                Title
            </label>

            <input type="text" wire:model="title" class="w-full rounded-xl border px-4 py-3">

            @error('title')
                <p class="text-sm text-red-500 mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">
                Description
            </label>

            <textarea wire:model="description" rows="4" class="w-full rounded-xl border px-4 py-3"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">

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
                    Assign To
                </label>

                <select wire:model="assigned_to" class="w-full rounded-xl border px-4 py-3">
                    <option value="">
                        Unassigned
                    </option>

                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">
                            {{ $member->name }}
                        </option>
                    @endforeach

                </select>
            </div>

        </div>

        <div>
            <label class="block text-sm font-medium mb-2">
                Due Date
            </label>

            <input type="date" wire:model="due_date" class="w-full rounded-xl border px-4 py-3">
        </div>

        <div class="flex justify-end">

            <button type="submit" class="rounded-xl bg-black px-5 py-3 text-white">
                Create Task
            </button>

        </div>

    </form>

</div>
