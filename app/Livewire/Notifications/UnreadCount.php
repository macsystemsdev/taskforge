<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class UnreadCount extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('notification-received')]
    #[On('notifications-read-state-changed')]
    public function refreshCount(): void
    {
        $this->count = auth()
            ->user()
            ->unreadNotifications()
            ->count();
    }

    public function render()
    {
        return view('livewire.notifications.unread-count');
    }
}
