# Week 5 Development Summary

# SaaS Billing & Subscription Infrastructure

## Overview

Week 5 focused entirely on building the commercial foundation of TaskForge.

What initially began as subscription billing evolved into a complete SaaS monetization infrastructure including:

* Subscription lifecycle management
* Stripe integration
* Scheduled plan transitions
* Automatic renewals
* Free trials
* Grace periods
* Feature restrictions
* Storage billing foundations
* Billing-aware authorization and UI

This became one of the largest architectural milestones completed so far.

---

# Subscription Architecture

## Core Subscription Model

Subscription responsibilities were significantly expanded.

### Features Added

* Active subscriptions
* Pending subscriptions
* Scheduled upgrades
* Scheduled downgrades
* Renewal eligibility
* Trial state management
* Grace period state management

---

# Subscription Lifecycle

The subscription domain now supports:

```text
Free
↓
Trial
↓
Paid
↓
Grace Period
↓
Downgrade
```

This provides realistic SaaS lifecycle behaviour.

---

# Plan Change Scheduling

Immediate activation is not always desirable.

### Example

```text
Enterprise Monthly
↓
User purchases Enterprise Yearly
↓
Remain on Monthly until expiry
↓
Automatically activate Yearly
```

### Infrastructure Added

* `pending_subscription_plan_id`
* `pending_effective_at`

### Services

* `ActivatePendingSubscriptionsService`
* `subscriptions:activate-pending`

---

# Subscription Renewal System

Implemented automatic subscription renewals.

## Renewal Flow

```text
Subscription nearing expiry
↓
Create renewal transaction
↓
Charge saved payment method
↓
Extend subscription period
```

### Infrastructure Added

* Stripe customer persistence
* Stored payment methods
* Off-session charging
* Renewal transaction handling

---

# Stripe Integration

Billing moved beyond simple checkout sessions.

## Implemented

### Checkout Sessions

* New subscriptions
* Upgrades

### Payment Intents

* Renewals
* Off-session charging

### Future Usage Support

```php
setup_future_usage => off_session
```

This allows future renewals without user interaction.

---

# Webhook Infrastructure

Implemented Stripe webhook processing.

## Supported Events

* `checkout.session.completed`
* `payment_intent.succeeded`
* `charge.succeeded`

---

# Webhook Deduplication

Stripe may resend events.

Implemented:

### WebhookEvent Model

Stores:

* provider
* event_id
* event_type
* processed_at

This guarantees idempotent processing.

---

# Free Trial System

Implemented a complete free trial workflow.

## Added

* `has_used_trial`
* `trial_starts_at`
* `trial_ends_at`

### Rules

Users:

* may only start one trial
* cannot restart trials
* may upgrade during trial

---

# Trial Upgrade Flow

```text
Free
↓
Trial
↓
User purchases Pro
↓
Trial cleared immediately
↓
Paid plan activated
```

---

# Grace Period System

Implemented protection against failed renewals.

## Lifecycle

```text
Subscription expires
↓
Renewal fails
↓
Grace period starts
↓
User updates payment
OR
Grace expires
↓
Downgrade to Free
```

### Added

* `grace_starts_at`
* `grace_ends_at`

---

# Feature Restrictions

Billing is now integrated directly into product capabilities.

## Limits Added

* max_workspaces
* max_projects
* max_members
* max_teams
* max_tasks
* max_storage_mb

---

# Organization Capability Helpers

Implemented:

* `canCreateWorkspace()`
* `canCreateProject()`
* `canCreateTask()`
* `canCreateTeam()`
* `canAddMember()`

---

# Feature Locking

No user data is deleted during downgrades.

## Rule

```text
Oldest resources remain active.
Newest resources become locked.
```

### Examples

```text
10 Workspaces
↓
Downgrade to Free
↓
1 Active
9 Locked
```

### Added Helpers

* `lockedWorkspaces()`
* `lockedProjects()`
* `lockedTasks()`
* `lockedTeams()`

---

# Billing-Aware Authorization

Policies now consider:

* Roles
* Plan restrictions
* Feature accessibility

This significantly reduced UI duplication.

---

# Billing User Experience

## Added

* Current plan indicators
* Scheduled plan indicators
* Trial cards
* Usage counters
* Upgrade CTAs
* Locked resource badges
* Disabled creation actions

### Usage Examples

```text
Projects: 14 / 100
Members: 4 / 5
Storage: 120 MB / 500 MB
```

---

# Storage Billing Foundation

Prepared infrastructure for future file uploads.

### Added

* `storage_used_bytes`
* `max_storage_mb`

This lays the groundwork for attachment billing.

---

# Architectural Lessons

This week reinforced an important principle:

> Billing is not merely payments.

Billing touches:

* Authorization
* Scheduling
* Lifecycle management
* Automation
* Feature access
* Data retention
* User experience
* Infrastructure design

Commercial SaaS systems become significantly more complex once monetization is introduced.

---

# Outcome

TaskForge now contains:

* Subscription infrastructure
* Stripe integration
* Automatic renewals
* Scheduled plan changes
* Webhook processing
* Duplicate protection
* Trials
* Grace periods
* Feature limits
* Feature locking
* Usage tracking
* Storage foundations
* Billing-aware authorization

Billing MVP is now complete and provides the commercial foundation required for future production deployment.
