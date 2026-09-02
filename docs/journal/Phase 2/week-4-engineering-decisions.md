# Week 4 Engineering Decisions

## 1. Reverb is the WebSocket transport

TaskForge uses Laravel Reverb instead of introducing a separate realtime backend.

```text
Laravel → Reverb → Echo → Browser → Livewire/UI
```

This keeps realtime infrastructure aligned with the Laravel application.

## 2. Echo is the browser client

`window.Echo` is the Echo client instance used by browser JavaScript.

It provides APIs such as:

```js
Echo.private(...)
Echo.join(...)
```

and presence callbacks:

```js
.here(...)
.joining(...)
.leaving(...)
```

Echo is not Reverb. Reverb is the server; Echo is the browser-side client abstraction.

## 3. Persistent notifications remain persistent

Database notifications remain the durable source.

Realtime broadcasting is an additional delivery path.

```text
Database = persistence
Reverb = immediate delivery
```

A disconnected user can still retrieve notifications later.

## 4. User notifications use private channels

The user channel is:

```text
App.Models.User.{id}
```

The authorization callback verifies that the authenticated user owns the requested channel.

## 5. Presence is project-scoped

The project presence channel is:

```text
presence-project.{projectId}
```

The product currently cares about who is actively viewing the same project, not who is globally online.

## 6. Existing authorization remains authoritative

Presence authorization must respect the same project access rules already enforced by the application's policies.

Presence must never become a second, weaker authorization system.

## 7. Presence payloads stay minimal

Only the data needed by the UI is returned:

```php
[
    'id' => $user->id,
    'name' => $user->name,
]
```

There is no reason to broadcast a full User model.

## 8. No last-active timestamp yet

A last-active timestamp was considered but deferred.

Current semantics remain:

```text
Viewing project = present
Not viewing project = absent
```

## 9. Typing uses whispers

Typing is transient and does not belong in persistent storage.

```js
.whisper('typing', ...)
.listenForWhisper('typing', ...)
```

This avoids creating database records for every keystroke.

## 10. Typing is throttled and expires

Typing whispers are throttled to approximately one second.

Typing state expires after approximately 2.5 seconds.

This limits traffic and prevents stale indicators.

## 11. JavaScript and Livewire have separate responsibilities

Echo belongs to the browser JavaScript layer.

Livewire belongs to server-backed UI state.

The bridge is:

```js
window.Livewire.dispatch(...)
```

This keeps transport-specific code out of the domain layer.

## 12. Livewire navigation requires channel lifecycle management

TaskForge uses `wire:navigate`, so realtime initialization cannot assume a traditional full page reload.

The application listens to:

```js
livewire:navigated
```

and explicitly leaves old channels before joining new project channels.

## 13. `window.TaskForge` is a configuration bridge, not a hard dependency

User identity can come from:

```text
window.TaskForge
meta tags
project DOM data attributes
```

This protects realtime initialization against lifecycle/timing issues.

## 14. Broadcast authorization and WebSocket connection are separate

A working WebSocket connection does not guarantee channel authorization.

A private/presence channel can still fail at:

```text
POST /broadcasting/auth
```

with 403.

Debug these layers independently.

## 15. Realtime does not automatically update Livewire collections

Receiving a broadcast does not automatically mutate:

```php
$comments
```

or another existing server-backed collection.

The receiving component needs an explicit state update, refresh, or reconciliation strategy.

## 16. Comments are evolving toward project collaboration

The current direction is not to create a redundant standalone chat system.

The existing project comment surface can evolve toward:

```text
conversation
blocker
solution
reusable project knowledge
```

A blocker can remain attached to useful project/task context so later team members can learn from it.

## 17. Frontend builds are part of the workflow

The browser consumes the compiled asset bundle.

Therefore:

```text
resources/js
      ↓
npm run build
      ↓
compiled bundle
      ↓
browser
```

Changing source without rebuilding can produce false debugging signals.

## Decision Summary

| Area | Decision |
|---|---|
| WebSocket server | Laravel Reverb |
| Browser realtime client | Laravel Echo |
| Persistent notification storage | Database notifications |
| User notifications | Private channel |
| Project presence | Presence channel |
| Presence scope | Per project |
| Presence identity | ID + name |
| Typing transport | Presence whisper |
| Typing persistence | None |
| Typing throttle | ~1 second |
| Typing expiry | ~2.5 seconds |
| UI bridge | Livewire events |
| Navigation lifecycle | `livewire:navigated` |
| Global last-active | Deferred |
| Separate chat system | Not currently justified |
| Comment direction | Project collaboration |
| Frontend asset workflow | Explicit build |
