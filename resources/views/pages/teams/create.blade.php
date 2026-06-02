<x-layouts::app>

    <div class="max-w-3xl mx-auto space-y-6">
        @livewire('teams.create-team', ['organization' => $organization])
    </div>

</x-layouts::app>