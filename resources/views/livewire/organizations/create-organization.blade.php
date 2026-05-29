<?php

use App\Data\Organizations\CreateOrganizationData;
use App\Models\Organization;
use App\Services\OrganizationService;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public function createOrganization(OrganizationService $organizationService): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Organization::where('slug', Str::slug((string) $value))->exists()) {
                        $fail(__('An organization with that name already exists.'));
                    }
                },
            ],
        ]);

        $organizationData = new CreateOrganizationData(name: $this->name, owner_id: auth()->id());

        $organization = $organizationService->create($organizationData);

        Flux::toast(variant: 'success', text: __('Organization created successfully.'));

        $this->redirectRoute('organizations.show', ['organization' => $organization], navigate: true);
    }
};

?>

<x-ui.page size="3xl">
    <x-ui.page-header
        :title="__('Create Organization')"
        :description="__('Set up a durable operating space for workspaces, projects, members, and invitations.')"
    />

    <x-ui.card class="space-y-6">
        <form wire:submit="createOrganization" class="space-y-6">
            <flux:input
                wire:model="name"
                :label="__('Organization Name')"
                type="text"
                placeholder="Acme Inc."
                required
                autofocus
            />

            <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    You can rename the organization later.
                </p>

                <flux:button variant="primary" type="submit">
                    Create Organization
                </flux:button>
            </div>
        </form>
    </x-ui.card>
</x-ui.page>
