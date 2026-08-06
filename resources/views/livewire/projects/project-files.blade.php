<x-ui.card id="project-files-{{ $project->id }}" class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="tf-panel-title">Project Files</h2>
            <p class="tf-panel-subtitle">Browse project attachments with search, sort, and filters.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(140px,200px)_minmax(90px,140px)] w-full sm:w-auto">
            <label for="project-files-search" class="sr-only">Search files</label>
            <input id="project-files-search" wire:model.debounce.500ms="search" type="search" placeholder="Search files..."
                aria-label="Search files"
                class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70" />

            <label for="project-files-sort" class="sr-only">Sort files</label>
            <select id="project-files-sort" wire:model="sort" wire:change="resetPage"
                aria-label="Sort files"
                class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
            </select>

            <label for="project-files-per-page" class="sr-only">Files per page</label>
            <select id="project-files-per-page" wire:model="perPage" wire:change="resetPage"
                aria-label="Files per page"
                class="w-full appearance-none rounded-2xl border border-zinc-200 bg-white px-4 py-2 pr-8 text-sm shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-zinc-700 dark:bg-zinc-950/70">
                <option value="5">5 / page</option>
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
            </select>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-[1fr_140px]">
        <div>
            <label class="block text-sm font-medium text-zinc-600 dark:text-zinc-300">File type</label>
            <select wire:model="type" wire:change="resetPage" class="mt-2 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-300 focus:outline-none focus:ring-2 focus:ring-blue-200">
                <option value="all">All files</option>
                <option value="images">Images</option>
                <option value="documents">Documents</option>
                <option value="spreadsheets">Spreadsheets</option>
                <option value="others">Other files</option>
            </select>
        </div>

        <div class="flex items-end justify-end text-sm text-zinc-500" aria-live="polite">
            @if ($attachments !== null)
                <span>{{ $attachments instanceof \Illuminate\Contracts\Pagination\Paginator ? $attachments->total() : count($attachments) }} files</span>
            @endif
        </div>
    </div>

    @if (! $ready)
        <div class="space-y-3">
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
            <div class="h-12 rounded-2xl bg-zinc-100" aria-hidden="true"></div>
        </div>
    @elseif ($attachments && $attachments->isNotEmpty())
        <div class="space-y-3">
            @foreach ($attachments as $attachment)
                @php
                    $file = $attachment->storedFile;
                @endphp

                <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-zinc-950/80 hover:shadow-md transition">
                    <div class="grid grid-cols-[56px_1fr_auto] items-center gap-4">
                        <div class="h-14 w-14 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-900/40">
                            @if($file && str_starts_with($file->mime_type, 'image/') && isset($attachment->preview_url))
                                <img src="{{ $attachment->preview_url }}" alt="{{ $file->original_filename }}" class="h-14 w-14 object-cover rounded" />
                            @else
                                <div class="text-2xl">
                                    @if($file)
                                        @if(str_starts_with($file->mime_type, 'image/'))
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
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-zinc-950 dark:text-white text-sm">{{ $file->original_filename }}</p>
                            <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                <span>{{ $attachment->uploader->name }}</span>
                                <span aria-hidden="true">•</span>
                                <span>{{ $attachment->created_at->diffForHumans() }}</span>
                                <span aria-hidden="true">•</span>
                                <span>{{ $file ? $file->extension : '' }} · {{ $file ? number_format($file->size / 1024, 1) . ' KB' : '' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                                    <a href="{{ route('projects.attachments.view', [$project, $attachment]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-md border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-950/80 dark:text-zinc-100 dark:hover:bg-zinc-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M2 10a8 8 0 1116 0A8 8 0 012 10zm9-3a1 1 0 10-2 0v3a1 1 0 00.293.707l2 2a1 1 0 001.414-1.414L11 9.586V7z"/></svg>
                                <span>Preview</span>
                            </a>

                            <a href="{{ route('projects.attachments.download', [$project, $attachment]) }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-1.5 text-sm text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 3a1 1 0 000 2h14a1 1 0 100-2H3zM7 8a1 1 0 012 0v5h2V8a1 1 0 112 0v5a1 1 0 01-1 1H8a1 1 0 01-1-1V8z"/></svg>
                                <span>Download</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($attachments instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-zinc-500">
                    Showing {{ $attachments->firstItem() }}–{{ $attachments->lastItem() }} of {{ $attachments->total() }}
                </div>

                <div class=""> 
                    <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        {{ $attachments->links() }}
                    </nav>
                </div>
            </div>
        @endif
    @else
        <x-ui.empty-state title="No project files"
            description="Upload attachments from the discussion or use the file library once files exist." />
    @endif
</x-ui.card>
<script>
    (function () {
        function payload(e) { return Array.isArray(e.detail) ? e.detail[0] : e.detail; }

        function refresh() {
            var el = document.getElementById('project-files-{{ $project->id }}');
            if (!el) return;
            var id = el.getAttribute('wire:id');
            if (!id || !window.Livewire) return;
            try {
                var comp = window.Livewire.find(id);
                if (comp && typeof comp.call === 'function') {
                    // Ensure the component reloads attachments
                    comp.call('loadAttachments');
                }
            } catch (err) {
                // ignore
            }
        }

        window.addEventListener('attachmentsUploaded', function (e) { refresh(); });
        window.addEventListener('commentCreated', function (e) { refresh(); });
    })();
</script>
