# TaskForge Realtime Architecture — Week 4 Map

## Purpose

This document explains the journey from the Laravel application to Reverb, through Echo, and back into the application UI.

The goal is to understand the architecture, not merely memorize API calls.

---

# 1. Complete Flow

```text
┌────────────────────┐
│ Laravel Application│
│                    │
│ Actions            │
│ Notifications      │
│ Policies           │
│ Broadcast events   │
│ Livewire           │
└─────────┬──────────┘
          │
          │ broadcast
          ▼
┌────────────────────┐
│ Laravel Reverb     │
│ WebSocket server   │
└─────────┬──────────┘
          │
          │ WebSocket
          ▼
┌────────────────────┐
│ Laravel Echo       │
│ Browser client     │
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ Browser JavaScript │
│                    │
│ channel lifecycle  │
│ transient state    │
└─────────┬──────────┘
          │
          │ Livewire.dispatch()
          ▼
┌────────────────────┐
│ Livewire component │
│ server-backed state│
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ Browser UI         │
└────────────────────┘
```

---

# 2. What Reverb Does

Reverb is the WebSocket server.

Traditional HTTP:

```text
Browser → Laravel → Browser
```

Realtime:

```text
Browser ⇄ WebSocket ⇄ Reverb
```

Laravel remains responsible for application behavior.

Reverb provides the persistent WebSocket transport.

---

# 3. What `window.Echo` Means

Echo is the browser-side Laravel broadcasting client.

After Echo is initialized:

```js
import './echo';
```

the application uses:

```js
window.Echo
```

to interact with the realtime system.

`window.Echo` is not:

- Reverb
- Laravel
- a database
- Livewire

It is the JavaScript client interface for subscribing to channels and handling realtime events.

---

# 4. Why Echo Exists

Without Echo, browser code would need to deal directly with WebSocket protocol details and channel subscription mechanics.

Echo provides application-level operations:

```js
Echo.private(...)
Echo.join(...)
```

and lifecycle handlers:

```js
.here(...)
.joining(...)
.leaving(...)
```

and whisper APIs:

```js
.whisper(...)
.listenForWhisper(...)
```

The application therefore works with concepts such as:

```text
private channel
presence channel
user joined
user left
typing
notification
```

instead of raw WebSocket messages.

---

# 5. Which Echo Methods Are Standard?

These are Echo APIs:

```js
.private(...)
.join(...)
.here(...)
.joining(...)
.leaving(...)
.listenForWhisper(...)
.whisper(...)
.notification(...)
```

The callback variable names are application code.

For example:

```js
.joining((user) => {
    ...
})
```

`joining` is the Echo API.

`user` is our chosen variable name.

---

# 6. Private Channel Flow

TaskForge user notifications use:

```text
App.Models.User.{id}
```

Laravel authorizes:

```php
Broadcast::channel(
    'App.Models.User.{id}',
    function ($user, $id) {
        return (int) $user->id === (int) $id;
    }
);
```

Flow:

```text
Echo.private(...)
       |
       v
POST /broadcasting/auth
       |
       v
Laravel authentication
       |
       v
channel authorization
       |
       +---- denied → 403
       |
       +---- allowed
                |
                v
           subscription
```

A 403 therefore means the authorization boundary failed. It does not automatically mean Reverb is broken.

---

# 7. Presence Channel Flow

Project presence uses:

```text
presence-project.{projectId}
```

The client joins:

```js
window.Echo.join(channelName)
```

The channel returns:

```php
[
    'id' => $user->id,
    'name' => $user->name,
]
```

This identity is exposed to other connected members.

---

# 8. Presence Lifecycle

If John is already viewing Project 3 and Mac opens it:

```text
Mac
 |
 | Echo.join()
 v
broadcast authorization
 |
 v
Reverb
 |
 +--------------------------+
 |                          |
 v                          v
Mac gets here()        John gets joining()
```

If Mac leaves:

```text
Mac
 |
 | leave
 v
Reverb
 |
 v
John
 |
 v
leaving(Mac)
```

The application handles this lifecycle explicitly.

---

# 9. Livewire Navigation

TaskForge uses:

```blade
wire:navigate
```

Navigation therefore cannot be treated as a guaranteed full page reload.

The realtime code listens for:

```js
document.addEventListener(
    'livewire:navigated',
    ...
);
```

The lifecycle becomes:

```text
Project 3
   ↓
leave presence-project.3
   ↓
Project 1
   ↓
join presence-project.1
```

This prevents stale project subscriptions.

---

# 10. Typing Flow

Typing uses the existing presence connection.

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

Complete path:

```text
Textarea
   ↓
JavaScript
   ↓
Echo.whisper()
   ↓
Reverb
   ↓
Other Echo client
   ↓
JavaScript
   ↓
Livewire.dispatch()
   ↓
Livewire state
   ↓
Typing UI
```

Typing is intentionally not persisted.

---

# 11. Why Whispers Are Appropriate

Typing events are transient.

Persisting every keystroke would create useless database traffic.

Whispers allow ephemeral signaling through the realtime channel.

The browser throttles typing events to approximately one per second.

The receiving client expires typing state after approximately 2.5 seconds.

---

# 12. Notification Flow

Notifications have both persistence and realtime delivery:

```text
Laravel
   |
   +--------------------+
   |                    |
   v                    v
Database            Broadcast
                        |
                        v
                     Reverb
                        |
                        v
                       Echo
                        |
                        v
             notification(callback)
                        |
                        v
               Livewire.dispatch()
                        |
                        v
                        UI
```

The database branch is why notifications remain available after reconnecting.

The realtime branch is why they can appear immediately.

---

# 13. Livewire Is the UI State Boundary

Echo receives realtime transport events.

Livewire owns server-backed component state.

The bridge is:

```js
window.Livewire.dispatch(
    'project-user-typing',
    { user }
);
```

Livewire receives:

```php
#[On('project-user-typing')]
public function userTyping(array $user): void
{
    ...
}
```

This keeps realtime transport code separate from application UI state.

---

# 14. User Identity Resolution

Realtime code needs the current user's identity.

The original approach relied on:

```js
window.TaskForge.userId
```

This was not reliable enough during navigation/build lifecycle.

The final fallback chain is:

```text
window.TaskForge.userId
        ↓
meta[name="user-id"]
        ↓
#taskforge-project[data-user-id]
```

The project DOM also carries the user's name.

This makes the realtime client less sensitive to script execution timing.

---

# 15. Broadcast Authentication vs WebSocket Connection

These are different.

```text
Reverb WebSocket connection
```

means the browser can communicate with the realtime server.

It does not mean:

```text
user is authorized for every private/presence channel
```

Private/presence access requires:

```text
WebSocket
+
authenticated HTTP request
+
channel authorization
```

Therefore:

```text
Reverb connected
```

can coexist with:

```text
POST /broadcasting/auth → 403
```

---

# 16. Realtime Does Not Equal Automatic State Synchronization

This became obvious with simultaneous comments.

The actual pipeline is:

```text
comment saved
      ↓
comment broadcast
      ↓
Echo receives event
      ↓
component receives application event
      ↓
component state updated
      ↓
DOM rendered
```

Each step is separate.

A successful broadcast does not automatically modify:

```php
$comments
```

inside a Livewire component.

The receiving component must explicitly reconcile or refresh its state.

---

# 17. Frontend Build Pipeline

The browser executes the compiled asset, not the source file directly.

```text
resources/js/*.js
       ↓
npm run build
       ↓
compiled app-*.js
       ↓
browser
```

If the source changes but the bundle is not rebuilt, the browser can continue running old code.

This caused significant debugging confusion during Week 4.

---

# 18. Current Comment Direction

The comment section is increasingly becoming the project collaboration surface.

The emerging model is:

```text
Project collaboration
       |
       +-- conversation
       +-- blockers
       +-- solutions
       +-- reusable lessons
```

The goal is not to build an unnecessary second chat product beside comments.

A blocker can eventually become durable project knowledge that future team members can search and learn from.

The exact domain model remains intentionally open.

---

# 19. Responsibility Map

| Responsibility | Layer |
|---|---|
| Business rules | Laravel |
| Authorization | Laravel policies/channel auth |
| Persistent state | Database |
| Broadcast decision | Laravel |
| WebSocket transport | Reverb |
| Channel subscription | Echo |
| Transient browser behavior | JavaScript |
| Component state | Livewire |
| Rendering | Blade/Flux/Livewire |

---

# 20. Three Realtime Patterns

## Pattern A — Persistent notification

```text
Laravel
 → Database
 → Broadcast
 → Reverb
 → Echo
 → Livewire
 → UI
```

## Pattern B — Presence

```text
Browser
 → Echo.join()
 → /broadcasting/auth
 → Reverb
 → here/joining/leaving
 → Livewire
 → UI
```

## Pattern C — Typing

```text
Browser input
 → Echo.whisper()
 → Reverb
 → Echo.listenForWhisper()
 → Livewire
 → UI
```

Do not treat these three mechanisms as interchangeable.

---

# 21. Final Mental Model

Remember:

```text
                    APP
                     |
                     | "Something happened"
                     v
                  REVERB
                     |
                     | realtime transport
                     v
                   ECHO
                     |
                     | event/callback
                     v
                JAVASCRIPT
                     |
                     | UI state event
                     v
                 LIVEWIRE
                     |
                     v
                    UI
```

For reverse-direction collaboration:

```text
USER ACTION
    ↓
JAVASCRIPT
    ↓
ECHO
    ↓
REVERB
    ↓
OTHER ECHO CLIENTS
    ↓
OTHER JAVASCRIPT
    ↓
LIVEWIRE
    ↓
OTHER USERS' UI
```

That is the architecture to preserve as TaskForge's realtime system grows.
