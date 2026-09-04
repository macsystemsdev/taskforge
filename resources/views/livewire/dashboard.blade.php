<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-6 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-8 backdrop-blur">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                    {{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-blue-50 sm:text-base">
                    {{ __('Here\'s what\'s happening across your organizations.') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('organizations.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/30 backdrop-blur transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('New Organization') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Organizations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->organizations->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm12-12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zm0 12a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Workspaces</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalWorkspaces }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Projects</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalProjects }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition hover:-translate-y-0.5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-rose-50 dark:bg-rose-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tasks</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->totalTasks ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        {{-- Left column --}}
        <div class="space-y-6">
            {{-- Recent Projects --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Projects</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Latest activity across your organizations</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        View all →
                    </a>
                </div>

                @if ($this->recentProjects->isNotEmpty())
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($this->recentProjects as $project)
                            <a href="{{ route('projects.show', $project) }}"
                                wire:key="recent-project-{{ $project->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $project->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $project->workspace->name }} • {{ $project->tasks_count }} tasks</p>
                                </div>
                                <span class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition">→</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No projects yet.</p>
                    </div>
                @endif
            </div>

            {{-- Your Tasks --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Your Tasks</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Open tasks assigned to you</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        View all →
                    </a>
                </div>

                @if ($this->assignedTasks->isNotEmpty())
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($this->assignedTasks as $task)
                            <a href="{{ route('tasks.show', $task) }}"
                                wire:key="task-{{ $task->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $task->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $task->project->name }}</p>
                                </div>
                                <x-ui.status-badge :status="$task->status->value" size="sm" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No open tasks. You're all caught up!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right column --}}
        <aside class="space-y-6">
            {{-- Due Soon --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Due Soon</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Next 7 days</p>
                </div>

                @if ($this->dueSoonTasks->isNotEmpty())
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($this->dueSoonTasks as $task)
                            <a href="{{ route('tasks.show', $task) }}"
                                wire:key="due-task-{{ $task->id }}"
                                class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $task->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $task->project->workspace->name }}</p>
                                </div>
                                <span class="text-xs font-medium text-amber-600">{{ $task->due_date->format('M d') }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nothing due soon.</p>
                    </div>
                @endif
            </div>

            {{-- Your Organizations --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Your Organizations</h2>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($this->organizations as $org)
                        <a href="{{ route('organizations.show', $org) }}"
                            wire:key="org-{{ $org->id }}"
                            class="group flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $org->name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $org->workspaces_count }} workspaces • {{ $org->projects_count }} projects</p>
                            </div>
                            <span class="text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition">→</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
