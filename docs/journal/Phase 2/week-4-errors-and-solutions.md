# Week 4 Errors and Solutions

## 1. Bind mount caused stale JavaScript

### Symptom

Realtime behavior did not match the source code.

### Cause

The local source and compiled JavaScript being served by the running application were different.

### Solution

Verify the actual browser bundle and rebuild frontend assets when frontend source changes.

```bash
npm run build
```

### Lesson

Always distinguish source code from the compiled bundle actually executed by the browser.

---

## 2. Livewire RootTagMissingFromViewException

### Symptom

```text
Livewire\Exceptions\RootTagMissingFromViewException
```

### Cause

The Livewire view contained only conditional markup without a root element.

### Solution

Wrap the conditional output:

```blade
<span>
    @if ($count)
        <span>...</span>
    @endif
</span>
```

### Lesson

Every Livewire component view needs a valid root element.

---

## 3. Notification dropdown disappeared

### Symptom

The notification button/dropdown was not visible after replacing custom markup.

### Cause

The custom notification UI was not using the Flux component structure correctly.

### Solution

Use Flux primitives:

```blade
<flux:dropdown>
    <flux:button ... />
    <flux:menu>
        ...
    </flux:menu>
</flux:dropdown>
```

### Lesson

Use the established UI framework primitives rather than recreating their behavior manually.

---

## 4. Notification delivered but unread UI did not update

### Symptom

The notification existed, but the unread count did not always update immediately.

### Cause

Realtime delivery and Livewire component state are separate.

### Solution

Dispatch an explicit Livewire event:

```js
window.Livewire.dispatch(
    'notification-received',
    { notification }
);
```

and handle it with:

```php
#[On('notification-received')]
```

### Lesson

Transport success does not imply UI-state synchronization.

---

## 5. `/broadcasting/auth` returned 403

### Symptom

```text
POST /broadcasting/auth 403
```

during presence subscription.

### Investigation

The WebSocket connection itself could be working while broadcast authorization failed.

The application required authenticated broadcast routes:

```php
Broadcast::routes(['middleware' => ['auth']]);
```

### Solution

Verify:

1. authenticated session
2. broadcast routes
3. channel name
4. channel authorization callback
5. project authorization rules

### Lesson

WebSocket connectivity and channel authorization are separate layers.

---

## 6. `window.TaskForge.userId` was unavailable

### Symptom

```text
[Presence] User ID not available
```

### Cause

The global JavaScript configuration object was not reliably available during the Livewire/frontend lifecycle.

### Solution

Use fallbacks:

```js
window.TaskForge?.userId
document.querySelector('meta[name="user-id"]')?.content
document.getElementById('taskforge-project')?.dataset?.userId
```

The project element also carries:

```html
data-user-id="..."
data-user-name="..."
```

### Lesson

Do not make realtime initialization depend on a single global variable being available at exactly one point in time.

---

## 7. Presence users did not appear

### Symptom

No users appeared in the project presence UI.

### Cause

The presence subscription was failing during authorization.

### Solution

Fix the authorization path before debugging `.here()`, `.joining()`, or `.leaving()`.

### Lesson

Prove subscription/authentication first. Then debug callbacks.

---

## 8. Typing indicators became stale

### Symptom

A user remained marked as typing after they stopped.

### Cause

Typing is ephemeral and has no persistent state that can expire automatically.

### Solution

Store timeout handles:

```js
const typingTimeouts = new Map();
```

Reset the timer when another typing event arrives and remove the user after approximately 2.5 seconds.

Also clear the timer when the user leaves the project.

### Lesson

Transient realtime state needs explicit expiry.

---

## 9. Simultaneous comments were not always visible immediately

### Symptom

John could send a comment and Mac would see it immediately. When Mac sent a comment milliseconds later, John could sometimes see it only after refresh.

### Cause

The server/database and realtime transport could be correct while the receiving Livewire component still held an older comments collection.

### Solution direction

Treat a comment-created event as a state synchronization event.

The receiver must explicitly reconcile or refresh its local comment state.

### Lesson

These are separate:

```text
message saved
message broadcast
message received
component state updated
DOM rendered
```

A working first three does not guarantee the last two.

---

## 10. Editor could not save a PHP file

### Symptom

```text
Failed to save 'CommentCreated.php': User did not grant permission.
```

### Cause

Local/container/Linux filesystem permissions.

### Solution

Use the container/Linux environment to correct file ownership/permissions as necessary.

### Lesson

Do not modify Laravel logic to solve a filesystem permission problem.

---

## 11. Frontend changes appeared not to work

### Symptom

Source JavaScript was correct but browser behavior remained old.

### Cause

The browser was consuming a previously compiled asset.

### Solution

Run:

```bash
npm run build
```

and verify the new bundle is loaded.

### Lesson

Build artifacts are part of the runtime.

---

# Realtime Debugging Checklist

Use this order.

## 1. Browser bundle

Is the current JavaScript actually loaded?

## 2. WebSocket

Is the Reverb connection established?

## 3. Channel

Is the exact intended channel being joined?

## 4. `/broadcasting/auth`

Does private/presence authorization return success?

## 5. Laravel authorization

Does the callback/policy permit the user?

## 6. Server event

Was the event/notification actually created/broadcast?

## 7. Echo callback

Does the appropriate callback fire?

```text
notification()
here()
joining()
leaving()
listenForWhisper()
```

## 8. Livewire dispatch

Does:

```js
window.Livewire.dispatch(...)
```

run?

## 9. Livewire listener

Does the corresponding:

```php
#[On(...)]
```

method run?

## 10. Component state

Does the state actually change?

## 11. DOM

Only now debug the rendered UI.

This order prevents wasting time rewriting the UI when the real failure is the build, connection, authorization, or state boundary.
