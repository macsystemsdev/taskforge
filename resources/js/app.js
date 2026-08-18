// Button loading is handled in Blade with scoped wire:loading/wire:target attributes.

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

// Set user data globally before other modules need it
window.TaskForge = {
    ...window.TaskForge,
    userId: document.querySelector('meta[name="user-id"]')?.content,
    userName: document.querySelector('meta[name="user-name"]')?.content,
};

// Import order matters - typing needs userId from global scope
import './presence';
import './notifications';