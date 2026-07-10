<x-layouts::app :title="__('Dashboard')">
    @php
        $organizations = auth()->user()->organizations()->with('workspaces.projects.tasks')->get();
        $ownedOrganizations = \App\Models\Organization::query()
            ->where('owner_id', auth()->id())
            ->with('workspaces.projects.tasks')
            ->get();
        $allOrganizations = $ownedOrganizations->merge($organizations)->unique('id')->values();
        $workspaces = $allOrganizations->flatMap->workspaces;
        $projects = $workspaces->flatMap->project;
        $tasks = $projects->flatMap->tasks;
        $assignedTasks = auth()->user()->assignedTasks()->with('project.team')->latest()->limit(6)->get();
        $unreadNotifications = auth()->user()->unreadNotifications()->count();
        $dueSoon = $tasks->filter(fn ($task) => $task->due_date && $task->due_date->isFuture() && $task->due_date->diffInDays(now()) <= 7)->count();
    @endphp

    <x-ui.page>
        <div class="mb-6 overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-zinc-950 via-zinc-900 to-indigo-950 p-6 text-white shadow-xl shadow-zinc-950/10 sm:p-8">
            <x-ui.page-header
                :title="__('Operations Dashboard')"
                :description="__('A focused view of organizations, project load, assigned work, and notification pressure.')"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card class="space-y-2 border-zinc-200/80 bg-white/90 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Organizations</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $allOrganizations->count() }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2 border-zinc-200/80 bg-white/90 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Projects</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $projects->count() }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2 border-zinc-200/80 bg-white/90 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Tasks due soon</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $dueSoon }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2 border-zinc-200/80 bg-white/90 shadow-sm">
                <p class="text-sm font-medium text-zinc-500">Unread notifications</p>
                <p class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $unreadNotifications }}</p>
            </x-ui.card>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-white/10">
                    <h2 class="tf-panel-title">Assigned work</h2>
                    <p class="tf-panel-subtitle">Tasks currently routed to you.</p>
                </div>

                @if ($assignedTasks->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignedTasks as $task)
                                    <tr class="tf-row-link">
                                        <td>
                                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-zinc-950 hover:underline dark:text-white" wire:navigate>
                                                {{ $task->title }}
                                            </a>
                                        </td>
                                        <td>{{ $task->project->name }}</td>
                                        <td><x-ui.status-badge :status="$task->status" /></td>
                                        <td><x-ui.priority-badge :priority="$task->priority" /></td>
                                        <td>{{ $task->due_date?->format('M d, Y') ?? 'No date' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-5">
                        <x-ui.empty-state title="No assigned tasks" description="Assigned work will appear here as projects start moving." />
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="tf-panel-title">Project load</h2>
                <p class="tf-panel-subtitle">A compact scan of current projects by task count.</p>

                <div class="mt-5 space-y-3">
                    @forelse ($projects->take(6) as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-white/10 dark:hover:bg-white/[0.03]" wire:navigate>
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $project->tasks->count() }} tasks</span>
                            </div>
                            <p class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $project->workspace->name }}</p>
                        </a>
                    @empty
                        <x-ui.empty-state title="No projects yet" description="Create a project from an organization workspace to start tracking work." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </x-ui.page>
</x-layouts::app>
