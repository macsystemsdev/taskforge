<x-layouts::app :title="__('Edit Team')">
    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 via-indigo-500/85 to-blue-600/90 p-5 text-white shadow-[0_8px_32px_rgba(37,99,235,0.15)] sm:p-6 backdrop-blur">
            <a href="{{ route('teams.index') }}" class="text-xs font-medium uppercase tracking-[0.15em] text-blue-100 hover:text-white">
                ← Teams
            </a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white">{{ __('Edit Team') }}</h1>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            @livewire('pages::teams.edit', ['team' => $team])
        </div>
    </div>
</x-layouts::app>
