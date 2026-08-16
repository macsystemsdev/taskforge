// Button loading is handled in Blade with scoped wire:loading/wire:target attributes.

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

console.log('TASKFORGE APP.JS LOADED');

import './echo';

console.log('Echo after import:', window.Echo);

const userId = window.TaskForge?.userId;

console.log('TaskForge user ID:', userId);

if (userId) {
    console.log(
        `Subscribing to App.Models.User.${userId}`
    );

    window.Echo
        .private(`App.Models.User.${userId}`)
        .notification((notification) => {
            console.log(
                'Realtime notification received:',
                notification
            );
        });
}
