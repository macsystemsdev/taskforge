# Week 1 Engineering Journal

**Project:** TaskForge
**Week:** 1
**Focus:** Owner Dashboard Foundation & Billing Domain Evolution

---

# Overview

Week 1 marked one of the largest architectural milestones of TaskForge.

Although the roadmap originally focused on building the Owner Dashboard, the work naturally evolved into a redesign of the entire billing domain after identifying a fundamental flaw in traditional CRUD-based subscription plan management.

Rather than simply completing dashboard widgets, this week established long-term architectural foundations that will affect every billing feature developed in the future.

Major outcomes included:

* Production-ready Owner Dashboard.
* Metrics service architecture.
* Dashboard caching strategy.
* Infrastructure monitoring.
* Revenue analytics.
* Organization health monitoring.
* Immutable Subscription Plan architecture.
* Subscription Plan lifecycle redesign.
* Metadata architecture.
* Owner administrative actions.
* Premium pricing preparation.
* Activity logging integration.
* Dashboard preview architecture.
* DTO-driven billing actions.

No shortcuts or temporary implementations were accepted.

Every implementation was evaluated from the perspective of a production SaaS platform.

---

# Week Objectives

Primary objectives:

* Build the Owner Dashboard.
* Separate dashboard responsibilities into reusable services.
* Introduce caching.
* Monitor platform health.
* Provide revenue analytics.
* Implement production-ready Subscription Plan administration.
* Finish Subscription Plan lifecycle.
* Prepare premium pricing infrastructure.

---

# Owner Dashboard Architecture

The Owner Dashboard was intentionally designed as an orchestration layer rather than a place where business logic lives.

A strict separation of responsibilities was adopted.

```
Database

↓

Domain Services

↓

Cache Services

↓

DTOs

↓

Widgets

↓

Dashboard
```

The dashboard became a consumer of business services instead of containing business logic itself.

This architecture ensures:

* reusable metrics
* reusable analytics
* reusable reporting
* API compatibility
* CLI compatibility
* scheduled reporting compatibility

Future reporting will reuse these services instead of duplicating queries.

---

# Dashboard Layout Decisions

The Owner Dashboard was organized into logical sections.

Platform Overview

Revenue

Revenue Growth

Subscription Breakdown

Organization Health

Usage Statistics

Infrastructure Monitoring

The ordering reflects administrator priorities.

Platform health should be visible immediately.

Infrastructure monitoring remains near the bottom because infrastructure issues are operational rather than commercial metrics.

---

# Widget Ordering

Final widget ordering became:

1. Platform Overview
2. Revenue Overview
3. Revenue Growth
4. Subscription Breakdown
5. Organization Health
6. Usage Statistics
7. Infrastructure

This ordering intentionally separates business metrics from operational metrics.

---

# Dashboard Polling

Dashboard widgets use polling selectively.

Real-time monitoring is valuable for:

* infrastructure
* queue health
* failed jobs

Revenue metrics do not require second-by-second updates.

Polling intervals were intentionally conservative to reduce unnecessary database load.

Production systems should avoid aggressive polling unless operationally necessary.

---

# Dashboard Service Architecture

Instead of allowing widgets to execute queries directly, every widget consumes dedicated services.

Responsibilities were divided into:

* Metrics services
* Cache services
* DTOs

Widgets only render data.

They never calculate business metrics.

This keeps widgets extremely thin.

---

# Cache Strategy

Caching was introduced immediately rather than postponed.

Reasons:

* Owner dashboard aggregates expensive queries.
* Multiple widgets require overlapping information.
* Dashboard pages are frequently refreshed.
* Future scheduled reporting will reuse identical metrics.

Rather than allowing each widget to hit the database independently, cache services became the single source of dashboard data.

---

# Metrics DTO Architecture

Dashboard metrics are transported through DTOs.

DTOs standardize:

* label
* value
* description
* icon
* color

Widgets therefore remain presentation-only.

Future consumers will receive identical data without duplicating transformation logic.

---

# Organization Metrics

Organization metrics included platform-wide statistics such as:

* total organizations
* active organizations
* subscription counts
* platform usage

Queries remain centralized inside services.

Widgets consume DTOs rather than models.

---

# Revenue Analytics

Revenue reporting was separated from platform metrics.

Revenue calculations included:

* Monthly Recurring Revenue
* Annual Recurring Revenue
* Monthly Growth
* Conversion Rate
* Subscription Breakdown
* Revenue Trends

Each calculation belongs to dedicated services.

Future exports and reports can reuse identical logic.

---

# Revenue Charts

Charts were intentionally isolated from revenue calculations.

Services calculate data.

Charts visualize data.

This separation avoids mixing visualization concerns with business logic.

Future chart implementations can change without affecting revenue calculations.

---

# Infrastructure Monitoring

Infrastructure monitoring became a first-class dashboard concern.

Metrics include:

* database health
* queue health
* failed jobs
* storage
* mail
* Redis availability

Infrastructure widgets report platform status without embedding operational logic inside the dashboard.

---

# Organization Health

Organization Health became more than a table.

Health represents a domain concept.

Instead of exposing raw numbers, health states summarize organizational quality.

Dedicated services calculate health.

Widgets only display results.

---

# Health Enum

Health values were represented through an Enum.

Benefits:

* type safety
* centralized labels
* reusable status comparisons
* presentation consistency

Health states can evolve without changing every widget.

---

# Health DTO

Health information was transported through DTOs.

The DTO standardized:

* organization
* health status
* score
* explanation

Future reporting and exports will reuse the same representation.

---

# Health Cache

Health calculations can become expensive as organizations grow.

Caching prevents recalculating identical scores repeatedly.

The cache service also becomes reusable for scheduled reports.

---

# Activity Logging

Administrative lifecycle actions were integrated with the existing activity logging infrastructure.

Instead of logging synchronously, actions dispatch the existing logging job.

Benefits:

* consistent audit history
* queue-driven logging
* reduced request latency
* reusable logging architecture

Owner actions therefore automatically become auditable.

---

# Billing Domain Review

While implementing Subscription Plan administration, a major architectural flaw was discovered.

Traditional CRUD editing of subscription plans breaks billing history.

Changing a plan's price rewrites the commercial agreement for existing customers.

That behavior is unacceptable for a production billing platform.

The original CRUD approach was abandoned.

---

# Immutable Subscription Plans

Subscription Plans are now treated as immutable commercial contracts.

Commercial attributes are never edited.

Changing commercial terms creates a new Subscription Plan.

Existing subscribers remain attached to their original commercial agreement.

Historical billing records therefore remain accurate forever.

This mirrors established SaaS billing practices.

---

# Commercial Attributes

The following fields were classified as commercial attributes:

* Price
* Billing Interval
* Workspace Limits
* Project Limits
* Team Limits
* Member Limits
* Task Limits
* Storage Limits
* Commercial Features

These fields define the commercial contract.

Editing them would rewrite history.

Instead, owners publish a new plan.

---

# Metadata Architecture

Not every field belongs to the commercial contract.

Marketing information changes frequently without affecting billing.

Metadata was separated into its own aggregate.

Metadata includes:

* Display Name
* Subtitle
* Description
* Badge
* Popular
* Recommended
* Accent Color
* Card Order
* Button Text
* Marketing Copy

This separation allows marketing teams to improve pricing presentation without altering commercial agreements.

---

# Why Metadata Was Separated

Separating metadata provides several advantages.

Commercial contracts remain immutable.

Marketing content remains editable.

Pricing cards become configurable.

Future localization becomes simpler.

Future A/B testing becomes possible.

Public pricing pages can evolve without affecting subscriptions.

---

# Subscription Plan Lifecycle

The lifecycle was redesigned.

Final lifecycle:

Draft

↓

Active

↓

Retired

↓

Archived

No backwards transitions are allowed.

Each transition represents a forward movement in the commercial lifecycle.

---

# Draft Plans

Draft is now the default state.

Plans are automatically created as Draft.

They cannot be purchased.

No Active checkbox exists during creation.

Activation becomes an intentional administrative decision.

---

# Active Plans

Active plans are available for purchase.

Customers may subscribe normally.

Plan changes may target active plans.

---

# Retired Plans

Retirement replaced deactivation.

The term "Deactivate" was rejected because it implied immediate removal.

Retirement better models commercial reality.

Retired plans:

* reject new subscriptions
* reject upgrades into the plan
* preserve existing subscribers
* preserve billing history

---

# Retirement Effective Date

Retirement requires a mandatory future effective date.

Immediate retirement was rejected.

Customers require notice before losing renewal eligibility.

Mandatory scheduling allows:

* customer communication
* operational planning
* billing continuity

---

# Customer Communication

Customers receive:

* Email
* In-app notification
* Dashboard banner

Customers remain responsible for selecting their replacement plan.

No automatic migration occurs.

---

# Renewal Policy

Existing subscribers continue renewing until the Retirement Effective Date.

After that date:

* renewal is blocked
* pending subscription activates
* customer must use an active plan

The existing scheduler will be extended rather than rewritten.

---

# Pending Subscription Workflow

A previous billing workflow already handled pending subscriptions.

Instead of building another migration system, the existing workflow will be reused.

Benefits:

* reduced complexity
* lower maintenance cost
* consistent billing behavior

---

# Subscription Plan Resource

The Subscription Plan Resource evolved beyond CRUD.

Administrative operations became lifecycle actions.

The resource now supports:

* Create
* View
* Manage Metadata
* Activate
* Schedule Retirement
* Archive

Editing commercial attributes was intentionally removed.

---

# Metadata Management

A dedicated metadata management page replaced generic editing.

This communicates the owner's intent clearly.

Administrators immediately understand they are editing presentation rather than pricing.

---

# Premium Pricing Card Preparation

A simplified preview component was introduced.

Its purpose is architectural rather than visual.

It establishes the integration point for the future production pricing card.

Future work will replace the simplified preview with the exact component used on the public pricing page.

This guarantees administrators preview the same experience customers receive.

---

# DTO-Driven Administrative Actions

Administrative actions continue the existing architecture.

Pages receive form input.

DTOs transport validated data.

Actions execute business rules.

Services remain reusable.

Pages contain almost no business logic.

---

# SaaS Principles Reinforced

Week 1 reinforced several permanent engineering principles.

Business-first implementation.

Production before convenience.

History is never rewritten.

Business rules belong inside Actions.

Pages orchestrate.

Services calculate.

DTOs transport.

Widgets render.

Cache services optimize.

Commercial contracts remain immutable.

Metadata remains flexible.

---

# Week Outcome

Week 1 delivered substantially more than originally planned.

The Owner Dashboard became production ready.

The billing domain evolved from CRUD administration into a commercial contract model suitable for long-term SaaS operation.

Most importantly, the architectural decisions made this week reduce future technical debt rather than creating it.

These foundations will support reporting, analytics, premium pricing, billing automation, customer lifecycle management, and future payment integrations without requiring fundamental redesign.
