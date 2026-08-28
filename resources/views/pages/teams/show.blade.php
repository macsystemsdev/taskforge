<x-layouts::app :title="$team->name">
    @livewire('teams.show-team', ['workspace' => $workspace, 'team' => $team])
</x-layouts::app>
