<?php

namespace App\Livewire\Billing;

use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ShowBillingSuccess extends Component
{
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        Gate::authorize('update', $organization);
        $this->organization = $organization;
    }
    
    public function render()
    {
        return view('livewire.billing.show-billing-success');
    }
}
