<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationCenter extends Component
{
    public function getNotificationsProperty()
    {
        return auth()
            ->user()
            ->notifications()
            ->latest()
            ->get();
    }

    public function getUnreadCountProperty(): int
    {
        return auth()
            ->user()
            ->unreadNotifications()
            ->count();
    }

    #[On('notification-received')]
    public function refreshNotifications(): void
    {
        //
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()
            ->user()
            ->notifications()
            ->findOrFail($notificationId);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $this->dispatch('notifications-read-state-changed');
    }

    public function markAllAsRead(): void
    {
        auth()
            ->user()
            ->unreadNotifications
            ->markAsRead();

        $this->dispatch('notifications-read-state-changed');
    }

    public function render()
    {
        return view('livewire.notifications.notification-center');
    }
}