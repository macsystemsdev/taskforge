<x-layouts::app :title="__('Projects')">
    @php
        $projects = \App\Models\Project::query()
            ->with(['workspace', 'team'])
            ->withCount('tasks')
            ->whereHas('team.members', fn($query) => $query->where('users.id', auth()->id()))
            ->latest()
            ->get();
    @endphp

    <x-ui.page>
        <x-ui.page-header :title="__('Projects')" :description="__(
            'Scan active project ownership, workspace context, dates, and task volume from one operational list.',
        )" />

        <x-ui.card padding="p-0" class="overflow-hidden">
            @if ($projects->isNotEmpty())
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Workspace</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th>Tasks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                <tr class="tf-row-link">
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="font-medium text-zinc-950 hover:underline dark:text-white"
                                            wire:navigate>
                                            {{ $project->name }}
                                        </a>
                                        @if ($project->description)
                                            <p class="mt-1 max-w-lg truncate text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $project->description }}</p>
                                        @endif
                                    </td>
                                    <td>{{ $project->workspace->name }}</td>
                                    <td>{{ $project->owner->name }}</td>
                                    <td><x-ui.status-badge :status="$project->status ?? 'active'" /></td>
                                    <td>{{ $project->due_date ? \Illuminate\Support\Carbon::parse($project->due_date)->format('M d, Y') : 'No date' }}
                                    </td>
                                    <td>{{ $project->tasks_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5">
                    <x-ui.empty-state title="No projects yet"
                        description="Create a project from a workspace to begin organizing tasks." />
                </div>
            @endif
        </x-ui.card>
    </x-ui.page>
</x-layouts::app>
