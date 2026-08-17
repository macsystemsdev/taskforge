<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationDropdown extends Component
{
    #[On('notification-received')]
    #[On('notifications-read-state-changed')]
    public function refreshNotifications(): void
    {
        unset($this->notifications);
        unset($this->unreadCount);
    }

    #[Computed]
    public function notifications()
    {
        return auth()
            ->user()
            ?->notifications()
            ->latest()
            ->take(3)
            ->get() ?? collect();
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()
            ->user()
            ?->unreadNotifications()
            ->count() ?? 0;
    }

    public function render()
    {
        return view('livewire.notifications.notification-dropdown');
    }
}