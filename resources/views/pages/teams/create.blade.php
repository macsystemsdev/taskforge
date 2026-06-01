<x-layouts::app>

    <div class="max-w-3xl mx-auto space-y-6">

        <flux:heading size="xl">
            Create Team
        </flux:heading>

        <flux:card>

            <form wire:submit="createTeam">

                <flux:input
                    wire:model="name"
                    label="Team Name"
                />

                <flux:textarea
                    wire:model="description"
                    label="Description"
                />

                <flux:button
                    type="submit"
                    variant="primary"
                >
                    Create Team
                </flux:button>

            </form>

        </flux:card>

    </div>

</x-layouts::app>