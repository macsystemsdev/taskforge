<x-layouts::app :title="__('Resource Locked')">
    <div class="flex min-h-[60vh] flex-col items-center justify-center px-4 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-500/20">
            <svg class="h-8 w-8 text-amber-600 dark:text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V8H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 7V5.5a3 3 0 10-6 0V8h6z" clip-rule="evenodd"/>
            </svg>
        </div>

        <h1 class="mt-6 text-2xl font-semibold text-zinc-950 dark:text-white">
            {{ __('Resource Locked') }}
        </h1>

        <p class="mt-3 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
            {{ $exception->getMessage() }}
        </p>

        <a href="{{ route('billing.index') }}"
           class="mt-8 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            {{ __('Upgrade Plan') }}
        </a>
    </div>
</x-layouts::app>
