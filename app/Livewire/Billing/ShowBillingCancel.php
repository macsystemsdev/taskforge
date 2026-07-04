<?php

namespace App\Livewire\Billing;

use App\Models\Organization;
use Livewire\Component;

class ShowBillingCancel extends Component
{
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        $this->organization = $organization;
    }
    public function render()
    {
        return view('livewire.billing.show-billing-cancel');
    }
}
