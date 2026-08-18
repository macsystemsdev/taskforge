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
            console.log('Realtime notification received:', notification);
            window.Livewire?.dispatch('notification-received', { notification });
        });
}

let currentProjectChannel = null;

function dispatchPresenceEvent(event, payload) {
    window.Livewire.dispatch(
        event,
        payload
    );
}

function joinProjectPresenceChannel() {
    const projectElement = document.getElementById(
        'taskforge-project'
    );

    const projectId = projectElement?.dataset?.projectId;

    if (!projectId) {
        leaveProjectPresenceChannel();

        return;
    }

    // CHANGE THIS LINE - Add 'presence-' prefix
    const channelName = `presence-project.${projectId}`;

    if (currentProjectChannel === channelName) {
        return;
    }

    leaveProjectPresenceChannel();

    currentProjectChannel = channelName;

    console.log(
        `[Presence] Joining ${channelName}`
    );

    window.Echo
        .join(channelName)
        .here((users) => {
            console.log(
                `[Presence] Users already in ${channelName}:`,
                users
            );

            dispatchPresenceEvent(
                'project-presence-here',
                {
                    users,
                }
            );
        })
        .joining((user) => {
            console.log(
                `[Presence] User joined ${channelName}:`,
                user
            );

            dispatchPresenceEvent(
                'project-presence-joining',
                {
                    user,
                }
            );
        })
        .leaving((user) => {
            console.log(
                `[Presence] User left ${channelName}:`,
                user
            );

            dispatchPresenceEvent(
                'project-presence-leaving',
                {
                    user,
                }
            );
        });
}

function leaveProjectPresenceChannel() {
    if (!currentProjectChannel) {
        return;
    }

    console.log(
        `[Presence] Leaving ${currentProjectChannel}`
    );

    window.Echo.leave(
        currentProjectChannel
    );

    currentProjectChannel = null;
}

joinProjectPresenceChannel();

document.addEventListener(
    'livewire:navigated',
    () => {
        joinProjectPresenceChannel();
    }
);
