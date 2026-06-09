<x-layouts::app>

    <div class="max-w-3xl mx-auto space-y-6">
        @livewire('teams.create-team', ['workspace' => $workspace])
    </div>

</x-layouts::app>