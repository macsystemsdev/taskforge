<x-ui.card wire:init="loadAttachments" class="space-y-8">
    {{-- Header Section --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6 dark:from-blue-950/20 dark:via-zinc-950 dark:to-indigo-950/20">
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/20">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Discussion</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Collaborate around decisions, blockers, and follow-up context.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm backdrop-blur dark:bg-white/5 dark:text-zinc-300">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    {{ $comments->count() }} of {{ $commentsTotal }} comments
                </div>
                <button id="jump-to-latest-{{ $commentable->id }}" type="button"
                    class="group inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:shadow-md hover:scale-105 dark:bg-zinc-800 dark:text-zinc-300">
                    <svg class="h-4 w-4 transition-transform group-hover:translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                    Latest
                </button>
            </div>
        </div>
    </div>

    {{-- CHAT DISCUSSION --}}
    <x-ui.card class="flex min-h-[600px] flex-col rounded-3xl border border-zinc-200/80 bg-gradient-to-b from-zinc-50/50 to-white shadow-xl shadow-zinc-200/50 backdrop-blur  dark:border-white/10 dark:from-zinc-900/50 dark:to-zinc-950 dark:shadow-black/20">
        {{-- Discussion Header --}}
        <div class="relative border-b border-zinc-200/80 bg-white/50 px-6 py-5 backdrop-blur dark:border-white/10 dark:bg-zinc-900/50">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-950/50">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Project Discussion</h3>
                        <p class="text-xs text-zinc-500">Real-time collaboration space</p>
                    </div>
                </div>
                
                @if (count($typingUsers))
                    <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                        <div class="flex space-x-1">
                            <span class="h-2 w-2 animate-bounce rounded-full bg-blue-500" style="animation-delay: 0ms"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-blue-500" style="animation-delay: 150ms"></span>
                            <span class="h-2 w-2 animate-bounce rounded-full bg-blue-500" style="animation-delay: 300ms"></span>
                        </div>
                        <span>
                            @if (count($typingUsers) === 1)
                                {{ collect($typingUsers)->first()['name'] }} is typing...
                            @elseif (count($typingUsers) === 2)
                                {{ collect($typingUsers)->pluck('name')->join(' and ') }} are typing...
                            @else
                                {{ count($typingUsers) }} people are typing...
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Comments List --}}
        <div class="overflow-hidden bg-gradient-to-b from-transparent to-zinc-50/30 dark:to-zinc-900/20">
            <div id="comments-list-{{ $commentable->id }}"
                class="flex h-full max-h-[480px] min-h-[320px] flex-col gap-5 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-zinc-300 scrollbar-track-transparent dark:scrollbar-thumb-zinc-700 scroll-smooth"
                aria-label="Recent comments">
                
                @if ($commentsTotal > $comments->count())
                    <div class="flex justify-center pb-2">
                        <button type="button" wire:click="loadMoreComments"
                            class="group inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-blue-950/40">
                            <svg class="h-4 w-4 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                            Load more comments
                        </button>
                    </div>
                @endif

                @forelse ($comments as $comment)
                    @php $isMe = optional(auth()->user())->id === $comment->user->id; @endphp
                    
                    @if ($isMe)
                        {{-- My Comment --}}
                        <div id="comment-{{ $comment->id }}" class="group flex gap-3 justify-end px-2">
                            <div class="max-w-[75%] space-y-2">
                                <div class="flex items-center justify-end gap-2 text-xs text-zinc-400">
                                    <span class="font-medium">You</span>
                                    <span>·</span>
                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="relative">
                                    <div class="inline-block rounded-2xl rounded-tr-sm bg-gradient-to-br from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-500/20 chat-bubble  group-hover:shadow-xl group-hover:shadow-blue-500/30">
                                        <p class="whitespace-pre-line text-sm leading-7">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                            <x-ui.avatar :name="$comment->user->name" size="lg" class="ring-2 ring-blue-500/20 transition-all group-hover:ring-blue-500/40" />
                        </div>
                    @else
                        {{-- Other User's Comment --}}
                        <div id="comment-{{ $comment->id }}" class="group flex gap-3 px-2">
                            <x-ui.avatar :name="$comment->user->name" size="lg" class="ring-2 ring-zinc-200/50 transition-all group-hover:ring-blue-300/50 dark:ring-white/10" />
                            
                            <div class="max-w-[75%] space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $comment->user->name }}</h3>
                                    <span class="text-xs text-zinc-400">·</span>
                                    <p class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="inline-block rounded-2xl rounded-tl-sm border border-zinc-200/80 bg-white shadow-sm chat-bubble  group-hover:shadow-md dark:border-white/10 dark:bg-zinc-900/80 dark:shadow-black/10">
                                    <p class="whitespace-pre-line text-sm leading-7 text-zinc-700 dark:text-zinc-300">{{ $comment->content }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex h-full items-center justify-center">
                        <div class="text-center space-y-4">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-950/40 dark:to-indigo-950/40">
                                <svg class="h-10 w-10 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">No comments yet</h3>
                                <p class="mt-1 text-sm text-zinc-500">Start the discussion by adding the first update or decision note.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

                {{-- Comment Input --}}
        <div class="border-t border-zinc-200/80 bg-white/80 px-6 py-5 backdrop-blur dark:border-white/10 dark:bg-zinc-900/80">
            <form wire:submit.prevent="createComment" class="space-y-4">
                <div class="flex items-end gap-2 relative z-10">
                    {{-- Attachment Button --}}
                    <label for="uploads-{{ $commentable->id }}" class="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:hover:bg-blue-950/60" style="display: flex !important; visibility: visible !important; opacity: 1 !important; position: relative; z-index: 50; min-width: 44px; min-height: 44px;"
                        class="group flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-500 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-white/10 dark:bg-zinc-950/60 dark:text-zinc-300 dark:hover:bg-blue-950/40 sm:h-12 sm:w-12" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                        <svg class="h-5 w-5" style="display: block !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                        <input id="uploads-{{ $commentable->id }}" wire:model="uploads" type="file" multiple class="hidden" />
                    </label>

                    {{-- Textarea --}}
                    <div class="relative">
                        <textarea class="flex-1 min-w-0" wire:model="content" x-data="{ lastTypingEvent: 0 }"
                            @input="
                                    const now = Date.now();
                                    if (now - lastTypingEvent > 1000) {
                                        window.TaskForge?.whisperProjectTyping();
                                        lastTypingEvent = now;
                                    }
                            "
                            placeholder="{{ __('Type a message...') }}" rows="3"
                            class="w-full resize-none rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-24 text-sm leading-6 text-zinc-900 shadow-sm transition focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-white/10 dark:bg-zinc-950/80 dark:text-white"></textarea>
                        <button type="submit" class="shrink-0"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Send
                        </button>
                    </div>

                    {{-- Upload Button - This was missing! --}}
                    <button type="button" wire:click="uploadAttachments" wire:loading.attr="disabled"
                        class="tf-button-secondary inline-flex h-12 shrink-0 px-4 py-0 text-sm" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important;">
                        Upload
                    </button>
                </div>

                {{-- Drop Zone --}}
                <div id="drop-zone-{{ $commentable->id }}"
                    class="rounded-xl border-2 border-dashed border-zinc-200 bg-white/80 px-4 py-3 text-sm text-zinc-500 transition hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:text-zinc-400">
                    Drag and drop media here, or tap the paperclip to attach files. Maximum size is 10MB per file.
                </div>

                {{-- Uploaded Files Preview --}}
                @if ($uploads)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($uploads as $index => $file)
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-sm text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                {{ $file->getClientOriginalName() }}
                                <span class="text-xs text-blue-500 dark:text-blue-400">
                                    {{ number_format($file->getSize() / 1024, 1) }} KB
                                </span>
                                <button type="button" wire:click="removeUpload({{ $index }})"
                                    class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-blue-200 dark:hover:bg-blue-900 transition-colors">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Error Messages --}}
                @error('content')
                    <p class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror

                @error('uploads')
                    <p class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror

                @error('uploads.*')
                    <p class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror

                {{-- Status Messages --}}
                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span wire:loading wire:target="uploadAttachments" role="status" aria-live="polite" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading attachments…
                    </span>
                    @if ($uploadSuccess)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Files uploaded successfully
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </x-ui.card>

    {{-- ATTACHMENT LIST --}}
    <x-ui.card class="space-y-6 overflow-hidden rounded-3xl border border-zinc-200/80 bg-white/50 backdrop-blur  hover:shadow-xl dark:border-white/10 dark:bg-zinc-900/50">
        <div class="flex items-center justify-between gap-4 border-b border-zinc-200/80 px-6 py-5 dark:border-white/10">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950/50">
                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Discussion Attachments</h3>
                    <p class="text-sm text-zinc-500">Files added directly to this project discussion</p>
                </div>
            </div>
        </div>

        @if (!$ready)
            <div class="space-y-3 px-6 pb-6">
                @for ($i = 0; $i < 3; $i++)
                    <div class="h-16 animate-pulse rounded-2xl bg-gradient-to-r from-zinc-100 via-zinc-50 to-zinc-100 dark:from-zinc-800 dark:via-zinc-900 dark:to-zinc-800" aria-hidden="true"></div>
                @endfor
            </div>
        @elseif ($attachments === null || $attachments->isEmpty())
            <div class="px-6 pb-6">
                <div class="flex flex-col items-center justify-center space-y-3 rounded-2xl border-2 border-dashed border-zinc-200 py-12 dark:border-white/10">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <svg class="h-8 w-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </div>
                    <div class="text-center">
                        <h4 class="font-semibold text-zinc-900 dark:text-white">No attachments yet</h4>
                        <p class="mt-1 text-sm text-zinc-500">Upload files to keep the discussion grounded in context</p>
                    </div>
                </div>
            </div>
        @else
            <div class="max-h-[460px] space-y-3 overflow-y-auto px-6 pb-6 scrollbar-thin scrollbar-thumb-zinc-300 scrollbar-track-transparent dark:scrollbar-thumb-zinc-700">
                @foreach ($attachments as $attachment)
                    @php $file = $attachment->storedFile; @endphp
                    <div class="group rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm  hover:shadow-lg hover:border-blue-200 dark:border-white/10 dark:bg-zinc-950/80 dark:hover:border-blue-900">
                        <div class="flex items-start gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-50 text-3xl shadow-inner transition-transform group-hover:scale-105 dark:from-zinc-800 dark:to-zinc-900">
                                {{ $this->attachmentIcon($file->mime_type) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold text-zinc-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                            {{ $file->original_filename }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2 text-sm text-zinc-500">
                                            <x-ui.avatar :name="$attachment->uploader->name" size="sm" />
                                            <span>{{ $attachment->uploader->name }}</span>
                                            <span>·</span>
                                            <span>{{ $attachment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('projects.attachments.download', [$commentable, $attachment]) }}"
                                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 hover:shadow dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-blue-950/40">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download
                                        </a>

                                        @if (str_starts_with($file->mime_type, 'image/') || $file->mime_type === 'application/pdf')
                                            <a href="{{ route('projects.attachments.view', [$commentable, $attachment]) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 hover:shadow dark:border-white/10 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-indigo-950/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Preview
                                            </a>
                                        @endif

                                        @can('update', $commentable)
                                            <button type="button" wire:click="deleteAttachment({{ $attachment->id }})"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition-all hover:bg-red-50 hover:border-red-300 hover:shadow dark:border-red-900/50 dark:bg-zinc-900 dark:text-red-400 dark:hover:bg-red-950/40">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        @endcan
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ $this->formatBytes($file->size) }}
                                    </span>
                                </div>

                                @if (str_starts_with($file->mime_type, 'image/') && isset($attachment->preview_url))
                                    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 shadow-inner dark:border-white/10">
                                        <img src="{{ $attachment->preview_url }}"
                                            alt="{{ $file->original_filename }}" 
                                            class="h-40 w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($attachmentsTotal && $attachmentsTotal > $attachments->count())
                <div class="flex justify-center border-t border-zinc-200/80 px-6 py-4 dark:border-white/10">
                    <button wire:click="loadMoreAttachments" 
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 transition-all hover:shadow-xl hover:shadow-blue-500/40 hover:scale-105">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Load More
                    </button>
                </div>
            @endif
        @endif
    </x-ui.card>
</x-ui.card>

{{-- Keep all your existing scripts and styles here --}}
<script>
    // All your existing JavaScript remains unchanged
    (function() {
        var commentsList = document.getElementById('comments-list-{{ $commentable->id }}');
        var scrollToBottom = function() {
            if (commentsList) {
                commentsList.scrollTop = commentsList.scrollHeight;
            }
        };

        document.addEventListener('livewire:load', scrollToBottom);
        window.addEventListener('load', scrollToBottom);

        var jumpButton = document.getElementById('jump-to-latest-{{ $commentable->id }}');
        if (jumpButton) {
            jumpButton.addEventListener('click', scrollToBottom);
        }

        function getPayload(e) {
            return Array.isArray(e.detail) ? e.detail[0] : e.detail;
        }

        window.addEventListener('attachmentsUploaded', function(e) {
            var payload = getPayload(e) || {};
            var count = payload.count ?? 0;
            var message = count + ' file' + (count === 1 ? '' : 's') + ' uploaded';

            try {
                if (window.flux && typeof window.flux.toast === 'function') {
                    window.flux.toast({
                        message: message,
                        type: 'success',
                    });
                    return;
                }
            } catch (err) {}

            var toast = document.createElement('div');
            toast.textContent = message;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 9999;
            toast.style.background = '#059669';
            toast.style.color = 'white';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.1)';
            toast.style.fontFamily = 'Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
            toast.style.fontSize = '13px';

            document.body.appendChild(toast);

            setTimeout(function() {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
            }, 2200);

            setTimeout(function() {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 2600);
        });
    })();
</script>

<script>
    (function() {
        window.addEventListener('attachmentsUploadFailed', function(e) {
            var payload = Array.isArray(e.detail) ? e.detail[0] : e.detail;
            var message = payload?.message ?? 'Upload failed';

            try {
                if (window.flux && typeof window.flux.toast === 'function') {
                    window.flux.toast({
                        message: message,
                        type: 'error',
                    });
                    return;
                }
            } catch (err) {}

            var toast = document.createElement('div');
            toast.textContent = message;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 9999;
            toast.style.background = '#dc2626';
            toast.style.color = 'white';
            toast.style.padding = '10px 14px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.1)';
            toast.style.fontFamily = 'Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
            toast.style.fontSize = '13px';

            document.body.appendChild(toast);

            setTimeout(function() {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-6px)';
            }, 3200);

            setTimeout(function() {
                if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
            }, 3600);
        });
    })();
</script>

<style>
    /* Keep all existing styles */
    .new-comment {
        animation: highlightComment 2.2s ease forwards;
        box-shadow: 0 8px 30px rgba(59, 130, 246, 0.08);
        transform-origin: top left;
    }

    @keyframes highlightComment {
        0% {
            background-color: rgba(59, 130, 246, 0.18);
            transform: translateY(-6px) scale(1.02);
            opacity: 0.98;
        }
        30% {
            background-color: rgba(59, 130, 246, 0.12);
            transform: translateY(0) scale(1.005);
            opacity: 1;
        }
        70% {
            background-color: rgba(59, 130, 246, 0.06);
            transform: translateY(0) scale(1);
        }
        100% {
            background-color: transparent;
            transform: none;
            opacity: 1;
        }
    }

    .enter-comment {
        animation: enterComment 360ms cubic-bezier(.2, .9, .2, 1) forwards;
    }

    @keyframes enterComment {
        0% {
            transform: translateY(8px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .chat-bubble {
        max-width: 85%;
        padding: 0.6rem;
    }

    @media (min-width: 640px) {
        .chat-bubble {
            max-width: 75%;
            padding: 0.75rem;
        }
    }

    @media (min-width: 768px) {
        .chat-bubble {
            max-width: 70%;
            padding: 1rem;
        }
    }

    .drop-zone-active {
        border-color: #2563eb;
        background-color: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
    }
</style>

<script>
    (function() {
        var dropZone = document.getElementById('drop-zone-{{ $commentable->id }}');
        var fileInput = document.getElementById('uploads-{{ $commentable->id }}');

        var preventDefault = function(event) {
            event.preventDefault();
            event.stopPropagation();
        };

        window.addEventListener('dragover', preventDefault);
        window.addEventListener('drop', preventDefault);

        if (dropZone && fileInput) {
            var setActive = function(active) {
                dropZone.classList.toggle('drop-zone-active', active);
            };

            dropZone.addEventListener('dragover', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(true);
            });

            dropZone.addEventListener('dragleave', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(false);
            });

            dropZone.addEventListener('drop', function(event) {
                event.preventDefault();
                event.stopPropagation();
                setActive(false);

                if (event.dataTransfer && event.dataTransfer.files.length) {
                    try {
                        fileInput.files = event.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    } catch (err) {
                        console.warn('Unable to assign dropped files to input.', err);
                    }
                }
            });
        }
    })();

    (function() {
        function getPayload(e) {
            return Array.isArray(e.detail) ? e.detail[0] : e.detail;
        }

        window.addEventListener('commentCreated', function(e) {
            var payload = getPayload(e) || {};
            var id = payload.id;

            try {
                var container = document.getElementById('comments-list-{{ $commentable->id }}');
                if (container) container.scrollTop = container.scrollHeight;
            } catch (err) {}

            if (!id) return;

            setTimeout(function() {
                try {
                    var el = document.getElementById('comment-' + id);
                    if (el) {
                        el.classList.add('enter-comment', 'new-comment');
                        setTimeout(function() {
                            el.classList.remove('new-comment');
                        }, 2400);
                        setTimeout(function() {
                            el.classList.remove('enter-comment');
                        }, 500);
                    }
                } catch (err) {}
            }, 120);
        });
    })();
</script>