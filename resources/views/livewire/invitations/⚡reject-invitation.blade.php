<?php

use Livewire\Component;

new class extends Component {
    public $invitation;
    public string $reason = '';

    public function rejectInvitation(): void
    {
        abort_if(auth()->user()->email !== $this->invitation->email, 403);

        abort_if(!$this->invitation->isPending(), 403);

        $this->invitation->update([
            'status' => 'rejected',
            'rejection_reason' => $this->reason,
        ]);

        Flux::toast(variant: 'success', text: 'Invitation rejected.');

        $this->redirectRoute('dashboard');
    }
};
?>

<div class="max-w-xl mx-auto">

    <flux:card class="space-y-6">

        <div>

            <flux:heading size="lg">
                Reject Invitation
            </flux:heading>

            <flux:subheading>
                Optionally provide a reason.
            </flux:subheading>

        </div>

        <form wire:submit="rejectInvitation" class="space-y-6">

            <flux:textarea wire:model="reason" label="Reason" placeholder="Optional reason..." />

            <div class="flex justify-end">

                <flux:button variant="danger" type="submit">
                    Reject Invitation
                </flux:button>

            </div>

        </form>

    </flux:card>

</div>
