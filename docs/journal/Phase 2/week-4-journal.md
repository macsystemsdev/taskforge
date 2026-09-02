# Week 4 Journal — Realtime Collaboration

## Week Objective

Week 4 introduced realtime collaboration into TaskForge.

Roadmap:
- Day 1 — Laravel Reverb architecture
- Day 2 — Live notifications
- Day 3 — Presence channels
- Day 4 — Typing indicators
- Day 5 — Realtime review and optimization

The week focused on understanding the architecture rather than merely installing packages.

Core flow:

```text
Laravel application
        |
        | broadcast
        v
     Reverb
        |
        | WebSocket
        v
      Echo
        |
        v
 Browser JavaScript
        |
        v
 Livewire / UI
```

---

## Day 1 — Laravel Reverb Architecture

Established Reverb as the WebSocket server and Echo as the browser-side client.

The important distinction:

- Laravel owns application rules.
- Reverb owns WebSocket transport.
- Echo owns browser-side channel interaction.
- JavaScript handles client-side realtime behavior.
- Livewire handles server-backed component state.
- Database state remains persistent truth.

The first target was live notifications.

---

## Day 2 — Live Notifications

Implemented realtime user notifications using a private user channel:

```php
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

The browser subscribes through Echo:

```js
window.Echo
    .private(`App.Models.User.${userId}`)
    .notification((notification) => {
        window.Livewire?.dispatch(
            'notification-received',
            { notification }
        );
    });
```

The notification remains persisted in the database while realtime broadcasting provides immediate delivery.

The notification UX was expanded to include:

- unread count
- individual read state
- mark all as read
- notification page
- realtime unread-count refresh
- latest-notifications dropdown

### Major debugging lesson

A bind mount/build mismatch caused the running application to execute different JavaScript from the source being edited.

The distinction between source files and the compiled bundle became explicit.

Frontend changes require rebuilding the bundle in the current container workflow:

```bash
npm run build
```

---

## Day 3 — Presence Channels

Implemented project-scoped presence:

```text
presence-project.{projectId}
```

The project page joins the channel through Echo and handles:

```js
.here(...)
.joining(...)
.leaving(...)
```

The authorization callback returns only:

```php
[
    'id' => $user->id,
    'name' => $user->name,
]
```

Presence is intentionally project-scoped.

Current semantics:

```text
Viewing project = present
Leaving project = absent
```

A global online/last-active system was considered but deferred because it adds historical activity semantics that are not required by the current collaboration UX.

Livewire navigation was handled with:

```js
document.addEventListener('livewire:navigated', ...)
```

so the old project channel is left and the new one is joined.

---

## Day 4 — Typing Indicators

Typing indicators reuse project presence.

Sender:

```js
currentProjectPresence.whisper('typing', {
    user: {
        id: userId,
        name: userName,
    },
});
```

Receiver:

```js
.listenForWhisper('typing', (event) => {
    handleProjectTyping(event.user);
});
```

Typing is deliberately not persisted.

Input is throttled to roughly one whisper per second, and typing state expires after roughly 2.5 seconds.

A `Map` stores per-user timeout handles:

```js
const typingTimeouts = new Map();
```

Leaving the project also clears typing state.

### User identity issue

`window.TaskForge.userId` proved unreliable during the Livewire/frontend lifecycle.

Fallbacks were added using DOM metadata/data attributes:

```text
window.TaskForge
    ↓
meta user-id
    ↓
#taskforge-project[data-user-id]
```

This made the realtime client resilient to navigation and script timing.

---

## Day 5 — Review and Optimization

Validated:

- private notification authorization
- notification delivery
- unread count updates
- notification read state
- mark-all-as-read
- notification dropdown
- project presence authorization
- project joining
- project leaving
- `here()`
- `joining()`
- `leaving()`
- typing whispers
- typing expiry
- Livewire navigation
- browser/frontend behavior
- simultaneous collaboration

A key lesson emerged from simultaneous comment testing:

> Realtime delivery does not automatically mutate an existing Livewire/Eloquent collection.

A comment can be saved and broadcast successfully while another Livewire component still holds an older collection. Realtime transport and UI-state reconciliation are separate concerns.

---

# End-of-Week Result

TaskForge now has three working realtime patterns:

1. Persistent realtime notifications
2. Project presence
3. Ephemeral typing indicators

The reusable pattern is:

```text
Authorize
   ↓
Subscribe
   ↓
Receive realtime event
   ↓
Translate event into application state
   ↓
Update UI
```
