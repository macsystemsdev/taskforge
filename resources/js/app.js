// Button loading is handled in Blade with scoped wire:loading/wire:target attributes.

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

const userId = window.TaskForge?.userId;

if (userId) {
    window.Echo
        .private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log(
                'Realtime notification received:',
                notification
            );

            window.Livewire.dispatch(
                'notification-received'
            );
        });
}