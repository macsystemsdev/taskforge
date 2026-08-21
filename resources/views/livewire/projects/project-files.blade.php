<x-ui.card id="project-files-{{ $project->id }}" class="!p-0 overflow-hidden">
    {{-- Header --}}
    <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Project Files</h2>
        <p class="mt-0.5 text-sm text-zinc-500">Browse project attachments</p>
    </div>

    <div class="space-y-4 p-6">
        {{-- Search & Filters --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="project-files-search" class="sr-only">Search files</label>
                <input id="project-files-search" wire:model.debounce.500ms="search" type="search"
                    placeholder="Search files..." aria-label="Search files"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70" />
            </div>

            <div>
                <label for="project-files-sort" class="sr-only">Sort files</label>
                <select id="project-files-sort" wire:model="sort" wire:change="resetPage" aria-label="Sort files"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>

            <div>
                <label for="project-files-per-page" class="sr-only">Files per page</label>
                <select id="project-files-per-page" wire:model="perPage" wire:change="resetPage"
                    aria-label="Files per page"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                    <option value="5">5 / page</option>
                    <option value="10">10 / page</option>
                    <option value="25">25 / page</option>
                </select>
            </div>
        </div>

        {{-- Type Filter --}}
        <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
            <div>
                <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300">File type</label>
                <select wire:model="type" wire:change="resetPage"
                    class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                    <option value="all">All files</option>
                    <option value="images">Images</option>
                    <option value="documents">Documents</option>
                    <option value="spreadsheets">Spreadsheets</option>
                    <option value="others">Other files</option>
                </select>
            </div>

            <div class="text-sm text-zinc-500" aria-live="polite">
                @if ($attachments !== null)
                    {{ $attachments instanceof \Illuminate\Contracts\Pagination\Paginator ? $attachments->total() : count($attachments) }}
                    files
                @endif
            </div>
        </div>

        {{-- Content --}}
        @if (!$ready)
            <div class="space-y-3">
                @for ($i = 0; $i < 3; $i++)
                    <div class="h-16 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800" aria-hidden="true"></div>
                @endfor
            </div>
        @elseif ($attachments && $attachments->isNotEmpty())
            <div class="space-y-3">
                @foreach ($attachments as $attachment)
                    @php $file = $attachment->storedFile; @endphp

                    <div
                        class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            {{-- Icon / Preview --}}
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-zinc-50 dark:bg-zinc-900/40">
                                @if ($file && str_starts_with($file->mime_type, 'image/') && isset($attachment->preview_url))
                                    <img src="{{ $attachment->preview_url }}" alt="{{ $file->original_filename }}"
                                        class="h-12 w-12 object-cover rounded-lg" />
                                @else
                                    <span class="text-xl">
                                        @if ($file)
                                            @if (str_starts_with($file->mime_type, 'image/'))
                                                🖼️
                                            @elseif($file->mime_type === 'application/pdf')
                                                📄
                                            @elseif(str_contains($file->mime_type, 'spreadsheet'))
                                                📊
                                            @else
                                                📎
                                            @endif
                                        @else
                                            📎
                                        @endif
                                    </span>
                                @endif
                            </div>

                            {{-- File Info --}}
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">
                                    {{ $file->original_filename }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-zinc-500">
                                    <span>{{ $attachment->uploader->name }}</span>
                                    <span aria-hidden="true">•</span>
                                    <span>{{ $attachment->created_at->diffForHumans() }}</span>
                                    <span aria-hidden="true">•</span>
                                    <span>{{ $file ? $file->extension : '' }} ·
                                        {{ $file ? number_format($file->size / 1024, 1) . ' KB' : '' }}</span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('projects.attachments.view', [$project, $attachment]) }}"
                                    target="_blank"
                                    class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950/80 dark:text-zinc-100 dark:hover:bg-zinc-900">
                                    Preview
                                </a>
                                <a href="{{ route('projects.attachments.download', [$project, $attachment]) }}"
                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white shadow-sm transition hover:bg-blue-700">
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($attachments instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-zinc-500">
                        Showing {{ $attachments->firstItem() }}–{{ $attachments->lastItem() }} of
                        {{ $attachments->total() }}
                    </div>
                    <nav class="inline-flex -space-x-px rounded-lg shadow-sm" aria-label="Pagination">
                        {{ $attachments->links() }}
                    </nav>
                </div>
            @endif
        @else
            <x-ui.empty-state title="No project files"
                description="Upload attachments from the discussion or use the file library once files exist." />
        @endif
    </div>
</x-ui.card>
