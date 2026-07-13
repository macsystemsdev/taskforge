# Billing Architecture

# Overview

TaskForge billing is organization-based.

Every organization owns exactly one subscription.

The billing system controls:

* Plan access
* Feature limits
* Resource locking
* Trials
* Renewals
* Grace periods
* Future usage billing

---

# Core Billing Hierarchy

Organization
↓
Subscription
↓
Subscription Plan
↓
Payment Transactions

---

# Subscription Lifecycle

Free
↓
Trial
↓
Paid Subscription
↓
Grace Period
↓
Downgrade to Free

---

# Subscription States

## Free

Default state.

Features:

* 1 Workspace
* 5 Projects
* 1 Team
* 5 Tasks
* 5 Members

---

## Trial

Duration:

14 Days

Behavior:

* Unlocks Pro features.
* Can only be used once.

After expiration:

* Activate pending plan if purchased.
* Otherwise return to Free.

---

## Paid

States:

* Active
* Pending Renewal
* Scheduled Plan Change

Supports:

* Monthly plans
* Yearly plans

---

## Grace Period

Purpose:

Prevent immediate lockout after payment failures.

Behavior:

* Subscription remains active temporarily.
* Billing notifications are sent.
* User can update payment method.

Expiration:

Downgrade to Free.

---

# Payment Lifecycle

Checkout
↓
Payment Transaction Created
↓
Stripe Checkout
↓
Webhook Verification
↓
Payment Completion
↓
Subscription Activation

---

# Renewal Flow

Renewal Date Reached
↓
Create Renewal Transaction
↓
Charge Stored Payment Method
↓
Payment Success
↓
Extend Subscription

OR

Payment Failure
↓
Start Grace Period
↓
Billing Notifications

---

# Plan Scheduling

Supported.

Users may purchase a new plan before the current one expires.

Behavior:

Current Plan
↓
Pending Plan
↓
Expiration
↓
Automatic Activation

---

# Feature Limits

Current Limits:

* Workspaces
* Projects
* Teams
* Tasks
* Members

---

# Resource Locking Strategy

If limits are exceeded after downgrade:

Oldest resources remain active.

Newest resources become locked.

Examples:

Workspace Limit:

1

Workspaces:

1. Default Workspace
2. Marketing Workspace
3. Engineering Workspace

Locked:

* Marketing
* Engineering

---

# Lock Hierarchy

Workspace Locked
↓
Teams Locked
↓
Projects Locked
↓
Tasks Locked

Team Locked
↓
Projects Locked
↓
Tasks Locked

Project Locked
↓
Tasks Locked

---

# Usage Tracking

Current:

* Workspace usage
* Project usage
* Team usage
* Task usage
* Member usage

Future:

* Storage usage
* API usage
* Analytics usage

---

# Billing Notifications

Planned:

* Trial Ending Reminder
* Renewal Reminder
* Payment Failed
* Grace Period Started
* Grace Period Expiring
* Subscription Renewed

---

# Queue Usage

Queued:

* Invitation Emails

Future:

* Billing Emails
* Reminder Emails
* Reports
* Usage Summaries

---

# Future Billing Features

## Storage Billing

Columns already prepared:

* storage_used_bytes
* max_storage_mb

Future features:

* File attachments
* Voice notes
* Media uploads

---

## Usage Analytics

Planned:

* Storage consumption
* Organization activity metrics
* Billing dashboards
* Growth metrics

---

## Seat Billing

Currently:

* max_members

Future:

* Per-seat pricing
* Team expansion pricing

---

## Coupons and Discounts

Planned:

* Promotional coupons
* Referral credits
* Temporary discounts

---

## Invoice System

Planned:

* PDF invoices
* Billing history
* Downloadable receipts

---

## Mobile Money

Planned payment providers:

* MTN Mobile Money
* Orange Money
* Cards

---

# Future Integrations

* Stripe Customer Portal
* Dunning Workflows
* Failed Payment Recovery
* Subscription Analytics
* Revenue Reporting

---

# Billing Philosophy

Billing should never destroy customer data.

Downgrades lock access.

They do not delete resources.

This preserves customer trust and enables reactivation without data loss.
