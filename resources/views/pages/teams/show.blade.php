<x-layouts::app>
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="text-sm text-zinc-500 hover:text-zinc-700">{{ $workspace->name }}</a>
                    <span class="text-zinc-300">/</span>
                </div>
                <flux:heading size="xl">{{ $team->name }}</flux:heading>
                @if($team->description)
                    <flux:subheading class="mt-2">{{ $team->description }}</flux:subheading>
                @endif
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('workspaces.show', $workspace) }}" variant="ghost" wire:navigate>
                    {{ __('Back') }}
                </flux:button>
            </div>
        </div>

        <!-- Team Info Card -->
        <flux:card>
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-zinc-500">{{ __('Team Members') }}</div>
                    <div class="text-2xl font-bold">{{ $team->members->count() }}</div>
                </div>
                <div>
                    <div class="text-sm text-zinc-500">{{ __('Created') }}</div>
                    <div class="text-sm">{{ $team->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="text-sm text-zinc-500">{{ __('Team ID') }}</div>
                    <div class="text-sm font-mono">{{ $team->slug }}</div>
                </div>
            </div>
        </flux:card>

        <!-- Team Members Section -->
        <flux:card>
            <flux:heading size="md" class="mb-4">{{ __('Team Members') }}</flux:heading>

            @if($team->members->count())
                <div class="divide-y">
                    @foreach($team->members as $member)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-zinc-200 flex items-center justify-center text-sm font-bold">
                                    {{ $member->initials() }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $member->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $member->email }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $role = auth()->user()->teamRole($team);
                                @endphp
                                <span class="text-sm px-2 py-1 rounded-full bg-zinc-100 text-zinc-700">
                                    {{ $member->pivot->role }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <flux:subheading>{{ __('No members yet') }}</flux:subheading>
                </div>
            @endif
        </flux:card>

        <!-- Team Projects Section (if applicable) -->
        <flux:card>
            <flux:heading size="md" class="mb-4">{{ __('Projects') }}</flux:heading>
            <div class="text-center py-8 text-zinc-500">
                {{ __('Projects will be available soon') }}
            </div>
        </flux:card>
    </div>
</x-layouts::app>
