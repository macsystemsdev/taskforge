<?php

use App\Services\OrganizationService;
use Flux\Flux;
use Livewire\Component;
use App\Data\Organizations\CreateOrganizationData;

new class extends Component {
    public string $name = '';

    public function createOrganization(OrganizationService $organizationService): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $organizationData = new CreateOrganizationData(name: $this->name, owner_id: auth()->id());

        $organization = $organizationService->create($organizationData);

        Flux::toast(variant: 'success', text: __('Organization created successfully.'));

        $this->redirectRoute('organizations.show', ['organization' => $organization], navigate: true);
    }
};

?>

<div class="min-h-screen flex items-center justify-center px-6 py-12">

    <div class="w-full max-w-2xl">

        <flux:card class="space-y-8">

            <div class="space-y-2">

                <flux:heading size="xl">
                    Create Organization
                </flux:heading>

                <flux:subheading>
                    Set up a new organization workspace for your team.
                </flux:subheading>

            </div>

            <form wire:submit="createOrganization" class="space-y-6">

                <flux:input wire:model="name" :label="__('Organization Name')" type="text" placeholder="Acme Inc."
                    required autofocus />

                <div class="flex items-center justify-between pt-4">

                    <p class="text-sm text-zinc-500">
                        You can rename this later.
                    </p>

                    <flux:button variant="primary" type="submit">
                        Create Organization
                    </flux:button>

                </div>

            </form>

        </flux:card>

    </div>

</div>
