@php
    try {
        $user = auth()->user();
        $organizations = $user ? ($user->organizations ?? collect()) : collect();
        $ownedOrganizations = $user ? \App\Models\Organization::query()
            ->where('owner_id', $user->id)
            ->with('workspaces.projects.tasks')
            ->get() : collect();

        $allOrganizations = $ownedOrganizations->merge($organizations)->unique('id')->values();
        $allOrganizationIds = $allOrganizations->pluck('id');
        $workspaces = $allOrganizations->flatMap->workspaces;
        $projects = $workspaces->flatMap->projects;
        $tasks = $projects->flatMap->tasks;

        $assignedTasks = $user ? $user->assignedTasks()->with('project.team')->latest()->limit(6)->get() : collect();
        $unreadNotifications = $user ? $user->unreadNotifications()->count() : 0;

        $dueSoon = \App\Models\Task::whereHas('project.workspace', function ($query) use ($allOrganizationIds) {
            $query->whereIn('organization_id', $allOrganizationIds);
        })
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->count();
    } catch (\Exception $e) {
        $allOrganizations = collect();
        $projects = collect();
        $tasks = collect();
        $assignedTasks = collect();
        $unreadNotifications = 0;
        $dueSoon = 0;
    }
@endphp

<x-layouts-app :title="__('Dashboard')">
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-zinc-800">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    <h1 class="text-2xl font-semibold">{{ __('Operations Dashboard') }}</h1>
                    
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-zinc-500">Organizations</p>
                            <p class="text-2xl font-bold">{{ $allOrganizations->count() }}</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-zinc-500">Projects</p>
                            <p class="text-2xl font-bold">{{ $projects->count() }}</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-zinc-500">Tasks due soon</p>
                            <p class="text-2xl font-bold">{{ $dueSoon }}</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <p class="text-sm text-zinc-500">Unread notifications</p>
                            <p class="text-2xl font-bold">{{ $unreadNotifications }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts-app>
