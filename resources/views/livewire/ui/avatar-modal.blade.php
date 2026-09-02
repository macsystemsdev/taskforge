<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    public bool $show = false;
    public ?string $avatarPath = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?int $avatarUserId = null;

    #[On('open-avatar-modal')]
    public function openAvatarModal($avatarPath = null, $name = null, $email = null, $userId = null): void
    {
        $this->avatarPath = $avatarPath;
        $this->name = $name;
        $this->email = $email;
        $this->avatarUserId = $userId ? (int) $userId : null;
        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->avatarPath = null;
        $this->name = null;
        $this->email = null;
    }

    public function render()
    {
        return view('livewire.ui.avatar-modal');
    }
};
?>

<div>
    <flux:modal wire:model="show" class="max-w-md">
        <div class="space-y-6 text-center">
            <div class="flex justify-center">
                @if ($avatarUserId)
                    <img src="{{ route('users.avatar', ['user' => $avatarUserId]) }}"
                         alt="{{ $name }}"
                         class="aspect-square w-64 rounded-full object-cover shadow-2xl sm:w-72"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23999%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 dominant-baseline=%22central%22 text-anchor=%22middle%22 font-size=%2280%22 fill=%22%23fff%22 font-family=%22sans-serif%22%3E{{ strtoupper(substr($name, 0, 1)) }}%3C/text%3E%3C/svg%3E';" />
                @else
                    <div class="flex aspect-square w-64 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-6xl font-bold text-white shadow-2xl sm:w-72">
                        {{ strtoupper(substr($name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div>
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $name }}</p>
                @if ($email)
                    <p class="mt-1 text-sm text-zinc-500">{{ $email }}</p>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
