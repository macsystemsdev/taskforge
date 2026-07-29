# TaskForge Architecture Decision Log (ADR)

**Purpose**

This document records permanent architectural decisions that define how TaskForge is designed.

Unlike the engineering journal, this document does **not** record implementation history.

Unlike the knowledge base, this document does **not** record bugs or troubleshooting.

Each Architecture Decision Record (ADR) captures a decision that should remain relevant long after the implementation details have changed.

---

# ADR-001 — Business-First Domain-Driven Architecture

**Status**

Accepted

**Date**

2026-07-29

## Context

TaskForge is intended to become a production SaaS platform rather than a CRUD application. As the project expanded, business rules quickly became more complex than simple model persistence.

## Decision

The application will be designed around business capabilities instead of database entities.

Every major feature will be modeled as a domain with clearly defined responsibilities.

The domain model is considered the source of truth.

## Alternatives Considered

* CRUD-first architecture
* Fat models
* Business logic inside controllers/pages

## Why They Were Rejected

These approaches tightly couple persistence with business rules, making future changes increasingly difficult.

## Consequences

### Positive

* Business rules remain centralized.
* Easier testing.
* Easier long-term maintenance.
* Supports future APIs, CLI commands and background jobs.

### Trade-offs

* More classes.
* Slightly higher initial development effort.

### Future Implications

Future modules should integrate naturally without requiring architectural redesign.

## Related Components

* Actions
* Services
* DTOs
* Domain Enums
* Policies

---

# ADR-002 — Thin Filament Pages

**Status**

Accepted

**Date**

2026-07-29

## Context

Administrative interfaces naturally encourage placing business logic inside resource pages.

## Decision

Filament Pages act only as orchestration layers.

Business rules belong inside Actions and Services.

## Alternatives Considered

* Business logic inside Filament Pages
* Business logic inside Livewire components

## Why They Were Rejected

UI technology should never become the owner of domain behavior.

## Consequences

### Positive

* Pages remain small.
* Business logic is reusable.
* Easier automated testing.
* Cleaner separation of concerns.

### Trade-offs

Additional Action classes are required.

### Future Implications

The same Actions can later be reused by APIs, scheduled jobs and CLI commands.

## Related Components

* Actions
* Services
* Filament Resources
* Livewire

---

# ADR-003 — Dashboard Architecture

**Status**

Accepted

**Date**

2026-07-29

## Context

Owner Dashboard widgets require overlapping metrics and expensive queries.

Embedding calculations inside widgets would create duplication.

## Decision

Dashboard data follows the pipeline:

Service

↓

Cache Service

↓

DTO

↓

Widget

## Alternatives Considered

* Widget → Database
* Widget → Model

## Why They Were Rejected

Widgets should never own business calculations.

## Consequences

### Positive

* Shared calculations.
* Shared caching.
* Consistent metrics.
* Reusable reporting foundation.

### Trade-offs

Additional service layer.

### Future Implications

Reporting and analytics will reuse these services.

## Related Components

* Dashboard Services
* Cache Services
* DTOs
* Widgets

---

# ADR-004 — Immutable Subscription Plans

**Status**

Accepted

**Date**

2026-07-29

## Context

Editing subscription pricing changes commercial agreements for existing customers.

Production SaaS billing systems avoid rewriting customer contracts.

## Decision

Subscription Plans represent immutable commercial contracts.

Commercial attributes are never edited.

Any commercial change creates a brand-new Subscription Plan.

## Alternatives Considered

* CRUD editing
* Version history inside one record
* Audit logs only

## Why They Were Rejected

Historical billing should not depend on reconstructing previous values.

## Consequences

### Positive

* Billing history remains accurate.
* Existing subscriptions remain valid.
* Historical invoices remain trustworthy.

### Trade-offs

More plan records over time.

### Future Implications

Supports enterprise-grade billing evolution.

## Related Components

* Subscription Plans
* Billing
* Actions
* Subscription Lifecycle

---

# ADR-005 — Metadata Is Separate From Commercial Attributes

**Status**

Accepted

**Date**

2026-07-29

## Context

Marketing information changes frequently while commercial contracts should remain immutable.

## Decision

Presentation metadata is stored independently from commercial plan attributes.

Metadata includes customer-facing information only.

## Alternatives Considered

* Store metadata directly on Subscription Plans

## Why They Were Rejected

Presentation and commercial data have different lifecycles.

## Consequences

### Positive

* Marketing becomes flexible.
* Commercial contracts remain protected.
* Future localization becomes simpler.
* Future A/B testing becomes possible.

### Trade-offs

Additional model and relationship.

### Future Implications

Supports premium pricing pages and richer customer experiences.

## Related Components

* SubscriptionPlanMetadata
* Metadata Actions
* Pricing UI

---

# ADR-006 — Subscription Plan Lifecycle

**Status**

Accepted

**Date**

2026-07-29

## Context

Subscription Plans require a commercial lifecycle rather than simple activation flags.

## Decision

Every Subscription Plan follows:

Draft

↓

Active

↓

Retired

↓

Archived

Backward transitions are prohibited.

## Alternatives Considered

* Active / Inactive
* Active / Deleted

## Why They Were Rejected

They fail to model commercial history.

## Consequences

### Positive

* Clear lifecycle.
* Predictable state transitions.
* Preserved commercial history.

### Trade-offs

Additional lifecycle management.

### Future Implications

Supports scheduled retirement and long-term billing evolution.

## Related Components

* SubscriptionPlanStatus
* Lifecycle Actions
* Billing

---

# ADR-007 — Mandatory Retirement Effective Date

**Status**

Accepted

**Date**

2026-07-29

## Context

Immediate retirement creates poor customer experience and unexpected billing interruptions.

## Decision

Retiring a plan always requires a future Retirement Effective Date.

Immediate retirement is not permitted.

## Alternatives Considered

* Immediate retirement
* Optional effective date

## Why They Were Rejected

Customers require advance notice before losing renewal eligibility.

## Consequences

### Positive

* Predictable customer communication.
* Operational stability.
* Better billing continuity.

### Trade-offs

Additional scheduling logic.

### Future Implications

Scheduler becomes responsible for lifecycle execution.

## Related Components

* RetireSubscriptionPlanAction
* Scheduler
* Notifications

---

# ADR-008 — Existing Subscribers Are Never Automatically Migrated

**Status**

Accepted

**Date**

2026-07-29

## Context

Automatic migration changes customer agreements without explicit consent.

## Decision

Existing subscribers remain on their original plan.

Customers choose their replacement plan.

## Alternatives Considered

* Automatic migration
* Forced migration

## Why They Were Rejected

Commercial contracts should not change without customer action.

## Consequences

### Positive

* Customer trust.
* Billing consistency.
* Clear commercial agreements.

### Trade-offs

Customers must actively select replacements.

### Future Implications

Migration remains a customer-driven workflow.

## Related Components

* Billing
* Notifications
* Subscription Renewal

---

# ADR-009 — Reuse Existing Pending Subscription Workflow

**Status**

Accepted

**Date**

2026-07-29

## Context

Retired plans require customers to transition onto new plans.

A pending subscription workflow already existed.

## Decision

Extend the existing pending subscription workflow instead of introducing another migration system.

## Alternatives Considered

* Dedicated migration workflow
* Automatic conversion process

## Why They Were Rejected

They duplicate existing capabilities and increase maintenance cost.

## Consequences

### Positive

* Lower complexity.
* Reduced maintenance.
* Consistent subscription behavior.

### Trade-offs

Existing workflow gains additional responsibilities.

### Future Implications

Future billing enhancements continue extending the same workflow.

## Related Components

* Pending Subscription
* Billing Scheduler
* Renewal

---

# ADR-010 — Reporting Reuses Dashboard Services

**Status**

Accepted

**Date**

2026-07-29

## Context

Reporting requires the same business metrics already calculated for the Owner Dashboard.

## Decision

Reporting consumes existing Services and Cache Services rather than introducing duplicate reporting queries.

## Alternatives Considered

* Independent reporting layer
* Dedicated reporting queries

## Why They Were Rejected

Duplicate business calculations inevitably drift apart over time.

## Consequences

### Positive

* Single source of truth.
* Consistent analytics.
* Lower maintenance.

### Trade-offs

Dashboard services become critical shared components.

### Future Implications

Exports, scheduled reports and analytics remain synchronized.

## Related Components

* Dashboard Services
* Cache Services
* Reporting
* Analytics

---

# ADR-011 — DTOs Define Application Boundaries

**Status**

Accepted

**Date**

2026-07-29

## Context

Business Actions receive input from multiple sources including Filament, future APIs, console commands and scheduled jobs.

Passing raw arrays into the application layer couples business logic to the presentation layer.

## Decision

All application boundaries use strongly typed Data Transfer Objects (DTOs).

Actions never receive raw request payloads.

UI layers translate transport data into DTOs before invoking business logic.

## Alternatives Considered

* Arrays passed directly into Actions
* Request objects inside Actions
* Business logic reading form state directly

## Why They Were Rejected

These approaches tightly couple Actions to a specific interface and reduce reusability.

## Consequences

### Positive

* Strong typing.
* Consistent validation.
* Reusable Actions.
* Clear application boundaries.

### Trade-offs

Additional DTO classes must be maintained.

### Future Implications

Every new interface—REST API, GraphQL, CLI or queue worker—can interact with the domain using the same contracts.

## Related Components

* DTOs
* Actions
* Filament Resources
* Future APIs
* Jobs
* Console Commands

---

# ADR Maintenance Policy

1. ADR numbers are permanent and never renumbered.
2. Accepted decisions remain in the log even if later superseded.
3. If a decision changes, create a new ADR and reference the previous one instead of editing history.
4. ADRs document architectural intent rather than implementation details.
5. Bug fixes, progress updates and troubleshooting belong in the engineering journal or knowledge base, not in this document.
6. Every future architectural decision that permanently influences TaskForge should be appended as a new ADR.
