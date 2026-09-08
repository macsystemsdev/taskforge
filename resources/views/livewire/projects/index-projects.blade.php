<?php

use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    #[Computed]
    public function projects()
    {
        $user = auth()->user();

        $activeOrgs = $user->activeOrganizations()->get();
        $activeOrgIds = $activeOrgs->pluck('id');

        // Gather locked project IDs from all active organizations
        $lockedProjectIds = collect();
        foreach ($activeOrgs as $org) {
            $lockedWorkspaceIds = $org->lockedWorkspaces()->pluck('id');
            $lockedTeamIds = $org->lockedTeams()->pluck('id');
            $lockedProjectIdsFromCount = $org->lockedProjects()->pluck('id');

            $lockedFromWorkspace = Project::whereIn('workspace_id', $lockedWorkspaceIds)->pluck('id');
            $lockedFromTeam = Project::whereIn('team_id', $lockedTeamIds)->pluck('id');

            $lockedProjectIds = $lockedProjectIds->merge($lockedProjectIdsFromCount)->merge($lockedFromWorkspace)->merge($lockedFromTeam);
        }
        $lockedProjectIds = $lockedProjectIds->unique();

        return Project::query()
            ->with(['workspace', 'team'])
            ->withCount('tasks')
            ->when($activeOrgIds->isEmpty(), fn($query) => $query->whereRaw('1 = 0'))
            ->when($activeOrgIds->isNotEmpty(), fn($query) => $query->whereHas('workspace.organization', fn($q) => $q->whereIn('id', $activeOrgIds)))
            ->when($lockedProjectIds->isNotEmpty(), fn($query) => $query->whereNotIn('projects.id', $lockedProjectIds))
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('workspace.organization', fn($q) => $q->where('owner_id', $user->id))
                    ->orWhereHas(
                        'workspace.organization.members',
                        fn($q) => $q
                            ->where('users.id', $user->id)
                            ->whereIn('organization_user.role', ['owner', 'admin'])
                            ->where('organization_user.status', 'active'),
                    )
                    ->orWhereHas('team.members', fn($q) => $q->where('users.id', $user->id));
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
};
?>

<div class="space-y-6">
    {{-- Header --}}
    <div
        class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">
                    {{ __('Projects') }}
                </h1>
                <p class="mt-2 text-sm text-blue-50">
                    {{ __('Scan active project ownership, workspace context, dates, and task volume.') }}
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Search projects..."
                    class="!bg-white/20 !text-white !placeholder-blue-100 !border-white/20" icon="magnifying-glass" />
            </div>

            <div class="flex gap-2 overflow-x-auto">
                @foreach (['all' => 'All', 'active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'] as $value => $label)
                    <button wire:click="$set('statusFilter', '{{ $value }}')"
                        class="rounded-full px-3 py-1.5 text-xs font-medium transition whitespace-nowrap
                            {{ $statusFilter === $value
                                ? 'bg-blue-600 text-white dark:bg-blue-500 dark:text-white'
                                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-white/10 dark:text-zinc-400 dark:hover:bg-white/15' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Projects Table --}}
    <x-ui.card padding="p-0" class="overflow-hidden border-zinc-200/80 bg-white/90 shadow-sm">
        @if ($this->projects->isNotEmpty())
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Workspace</th>
                            <th>Status</th>
                            <th>Due</th>
                            <th>Tasks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->projects as $project)
                            <tr class="tf-row-link cursor-pointer" wire:key="project-{{ $project->id }}"
                                onclick="window.location='{{ route('projects.show', $project) }}'">
                                <td>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $project->name }}</p>
                                    @if ($project->description)
                                        <p class="mt-1 max-w-lg truncate text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $project->description }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="text-sm text-zinc-600 dark:text-zinc-300">{{ $project->workspace->name }}</span>
                                </td>
                                <td><x-ui.status-badge :status="$project->status ?? 'active'" /></td>
                                <td>
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $project->due_date ? \Illuminate\Support\Carbon::parse($project->due_date)->format('M d, Y') : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700 dark:bg-white/10 dark:text-zinc-300">
                                        {{ $project->tasks_count }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-zinc-200 px-4 py-3 dark:border-white/10">
                {{ $this->projects->links() }}
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-sm font-medium text-zinc-950 dark:text-white">
                    {{ $search ? 'No projects found matching your search.' : 'No projects yet.' }}
                </p>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ $search ? 'Try a different search term.' : 'Create a project from a workspace to begin organizing tasks.' }}
                </p>
            </div>
        @endif
    </x-ui.card>
</div>
