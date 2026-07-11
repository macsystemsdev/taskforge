<?php

use App\Data\Organizations\CreateOrganizationData;
use App\Models\Organization;
use App\Services\OrganizationService;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Policies\OrganizationPolicy;

new class extends Component {
    public string $name = '';
    public string $workspace_name = '';

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
            'workspace_name' => ['required', 'string', 'max:255'],
        ]);

        $organizationData = new CreateOrganizationData(name: $this->name, owner_id: auth()->id(), workspace_name: $this->workspace_name);

        $organization = $organizationService->create($organizationData);

        Flux::toast(variant: 'success', text: __('Organization created successfully.'));

        $this->redirectRoute('organizations.show', ['organization' => $organization], navigate: true);
    }
};

?>

<x-ui.page size="3xl">
    <x-ui.page-header :title="__('Create Organization')" :description="__('Set up a durable operating space for workspaces, projects, members, and invitations.')" />

    <x-ui.card class="space-y-6">
        <form wire:submit="createOrganization" class="space-y-6">
            <flux:input wire:model="name" :label="__('Organization Name')" type="text" placeholder="Acme Inc." required
                autofocus />

            <flux:input wire:model="workspace_name" :label="__('Default Workspace Name')" type="text"
                placeholder="Default Workspace" required />

            <div
                class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    You can rename the organization later.
                </p>



                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="createOrganization" class="inline-flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="createOrganization">Create Organization</span>
                    <span wire:loading.flex wire:target="createOrganization" class="inline-flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Zm2.93 7.07A8 8 0 0 0 20 12h4a12 12 0 0 1-10.93 12Z"></path>
                        </svg>
                        <span>Creating...</span>
                    </span>
                </flux:button>
            </div>
        </form>
    </x-ui.card>
</x-ui.page>
