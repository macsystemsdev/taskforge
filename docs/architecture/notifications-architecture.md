# Notification Architecture

## Purpose

TaskForge uses a hybrid notification system composed of:

1. Database notifications
2. Email notifications
3. Future realtime notifications (Laravel Reverb)

The goal is to provide operational visibility without spamming users.

---

# Notification Principles

## Database Notifications

Used for:

* Task events
* Project events
* Membership events
* Billing alerts
* System warnings

Characteristics:

* Non-intrusive
* Persistent
* Read/unread state
* Deep-link support

Payload structure:

```php
[
    'title' => '',
    'message' => '',
    'url' => '',
    'icon' => '',
]
```

---

## Email Notifications

Reserved only for important events.

Emails should never be used for high-frequency operational activity.

### Email Events

* Organization Invitation
* Trial Ending Reminder
* Payment Failure
* Grace Period Warning
* Subscription Renewal Reminder
* Security Notifications

---

## Future Realtime Notifications

Planned for Week 6.

Will use:

* Laravel Reverb
* Broadcasting
* Database notifications as persistence layer

Realtime notifications should mirror database notifications.

---

# Notification Matrix

| Event                   | Recipient             | Database | Email    | Realtime |
| ----------------------- | --------------------- | -------- | -------- | -------- |
| Organization Invitation | Invited User          | No       | Yes      | No       |
| Invitation Accepted     | Organization Admins   | Yes      | No       | Future   |
| Invitation Rejected     | Organization Admins   | Yes      | No       | Future   |
| Task Assigned           | Assignee              | Yes      | Optional | Future   |
| Task Reassigned         | New Assignee          | Yes      | No       | Future   |
| Task Completed          | Project Leaders       | Yes      | No       | Future   |
| Task Cancelled          | Project Leaders       | Yes      | No       | Future   |
| Task Overdue            | Assignee + Leaders    | Yes      | Optional | Future   |
| Project Completed       | Team Leaders + Admins | Yes      | No       | Future   |
| Project Cancelled       | Team Leaders + Admins | Yes      | No       | Future   |
| Trial Ending            | Organization Owners   | Yes      | Yes      | Future   |
| Payment Failed          | Organization Owners   | Yes      | Yes      | Future   |
| Grace Period Started    | Organization Owners   | Yes      | Yes      | Future   |
| Subscription Renewed    | Organization Owners   | Yes      | Optional | Future   |

---

# Queue Strategy

All notifications that send emails must be queued.

Examples:

* Invitation emails
* Billing emails
* Reminder emails

# Notification Payload Standard

[
    'title',
    'message',
    'icon',
    'url',
    'actor_id',
    'actor_name',
    'created_at'
]

Queue driver:

```env
QUEUE_CONNECTION=database
```

Future:

```env
QUEUE_CONNECTION=redis
```

---

# Deep Linking

Every notification should contain:

* title
* message
* url
* icon

The URL allows users to navigate directly to the affected resource.

Examples:

Task Assigned:

```php
route('tasks.show', $task)
```

Project Completed:

```php
route('projects.show', $project)
```

Billing Warning:

```php
route('organizations.billing', $organization)
```

---

# Notification Philosophy

Important events interrupt users through email.

Operational events remain inside the application.

This prevents notification fatigue while maintaining visibility.


# Future Features

- Notification Preferences
- Notification Digests
- Browser Push Notifications
- Mobile Push Notifications
- Notification Categories
- Real-time Broadcasting