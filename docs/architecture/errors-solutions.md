# TaskForge Knowledge Base

## Error Solutions & Engineering Lessons

**Purpose**

This document serves as the permanent engineering knowledge base for TaskForge.

Unlike the weekly engineering journal, this document records problems encountered during development together with their resolutions and the architectural lessons learned.

Entries should never be removed.

New issues should be appended as the project evolves.

---

# Billing Domain

---

## KB-001 — CRUD Editing Breaks Billing History

### Context

While implementing Subscription Plan administration, the original CRUD editing workflow allowed commercial attributes to be modified after customers had already subscribed.

### Root Cause

The design assumed a subscription plan behaves like a normal CRUD entity.

Commercial subscription plans are contracts, not editable records.

### Why It Happened

The initial implementation focused on data persistence rather than commercial lifecycle.

Production SaaS platforms treat plans as immutable agreements.

### Fix

Subscription Plans were redesigned as immutable commercial contracts.

Commercial changes now create entirely new Subscription Plans.

### Why The Fix Works

Existing subscribers continue referencing the exact commercial agreement they originally accepted.

Historical billing data never changes.

### Prevention

Whenever a model represents a customer contract, assume immutability first.

### Files Affected

* SubscriptionPlan
* CreateSubscriptionPlanAction
* ActivateSubscriptionPlanAction
* RetireSubscriptionPlanAction
* ArchiveSubscriptionPlanAction

### Related Architecture Decisions

ADR-004

ADR-006

### Lessons Learned

Contracts should never be treated as editable CRUD resources.

---

## KB-002 — Metadata Mixed With Commercial Data

### Context

Pricing cards require marketing information that changes regularly.

Initially these fields were considered part of Subscription Plans.

### Root Cause

Marketing presentation and commercial contracts were stored together.

### Why It Happened

Presentation concerns were not separated from billing concerns.

### Fix

Created Subscription Plan Metadata as a dedicated concept.

Metadata became independently editable.

Commercial fields remain immutable.

### Why The Fix Works

Marketing evolves independently without affecting subscriptions.

### Prevention

Separate presentation from commercial agreements whenever possible.

### Files Affected

* SubscriptionPlanMetadata
* UpdateSubscriptionPlanMetadataAction
* Metadata DTOs
* Metadata Resource

### Related Architecture Decisions

ADR-005

### Lessons Learned

Customer presentation and commercial contracts have different lifecycles.

---

## KB-003 — Deactivate Was The Wrong Business Term

### Context

Subscription plans originally used a Deactivate action.

### Root Cause

Deactivate implied immediate removal.

Commercial products rarely disappear instantly.

### Fix

Replaced Deactivate with Retire.

Introduced retirement scheduling.

### Why The Fix Works

Retirement models the actual business lifecycle.

### Prevention

Use terminology that matches business language instead of technical language.

### Files Affected

* RetireSubscriptionPlanAction
* SubscriptionPlanStatus
* Owner Resource

### Related Architecture Decisions

ADR-006

ADR-007

### Lessons Learned

Naming should communicate business intent.

---

## KB-004 — Immediate Retirement Creates Billing Problems

### Context

Retiring plans immediately would prevent customers from preparing for migration.

### Root Cause

Lifecycle ignored customer transition periods.

### Fix

Mandatory Retirement Effective Date.

### Why The Fix Works

Customers receive notice before renewal stops.

### Prevention

Time-sensitive billing operations should always include an effective date.

### Files Affected

* RetireSubscriptionPlanAction
* Retirement DTO
* Owner Resource

### Related Architecture Decisions

ADR-007

### Lessons Learned

Commercial lifecycle changes should be scheduled, not instantaneous.

---

# DTO Issues

---

## KB-005 — Snake Case Form Fields Failed DTO Construction

### Context

Metadata updates failed during DTO creation.

### Exact Error

CannotCreateData

Missing constructor parameter:

DisplayName

### Root Cause

Filament submitted snake_case field names.

DTO constructor expected camelCase names.

### Why It Happened

Spatie Laravel Data performs property matching unless explicit mapping is configured.

### Fix

Mapped incoming field names using MapInputName attributes.

### Why The Fix Works

Presentation layer remains snake_case while application layer uses PHP naming conventions.

### Prevention

Whenever DTO names differ from UI field names, explicitly configure mappings.

### Files Affected

* UpdateSubscriptionPlanMetadataData

### Related Architecture Decisions

DTO boundaries.

### Lessons Learned

DTO naming should not depend on UI conventions.

---

## KB-006 — Raw Form Values Passed Into Actions

### Context

Retirement action expected a DTO.

A string date was passed directly.

### Exact Error

TypeError

Argument must be RetireSubscriptionPlanData.

String given.

### Root Cause

Filament page bypassed the DTO boundary.

### Fix

Construct DTO before calling the Action.

### Why The Fix Works

Business layer receives strongly typed objects.

### Prevention

Pages should never pass raw request data into Actions.

### Files Affected

* ViewSubscriptionPlan
* RetireSubscriptionPlanAction

### Related Architecture Decisions

Business-first Actions.

### Lessons Learned

Pages translate UI input.

Actions execute business rules.

---

## KB-007 — Carbon Type Mismatch

### Context

Retirement DTO expected Carbon.

Filament DatePicker returned string.

### Exact Error

TypeError

Carbon expected.

String received.

### Root Cause

UI boundary returned serialized date.

DTO required domain type.

### Fix

Convert using Carbon::parse before DTO construction.

### Why The Fix Works

Application layer now receives domain types instead of transport types.

### Prevention

Perform transport-to-domain conversion at application boundaries.

### Files Affected

* ViewSubscriptionPlan
* RetireSubscriptionPlanData

### Related Architecture Decisions

DTO boundaries.

### Lessons Learned

UI types and domain types are not always identical.

---

# Filament Issues

---

## KB-008 — Missing Section Class

### Context

Attempting to build metadata pages.

### Exact Error

Undefined type

Filament\Forms\Components\Section

### Root Cause

Filament 5 moved layout components into the Schema namespace.

### Fix

Use

Filament\Schemas\Components\Section

### Why The Fix Works

Filament 5 separates layout from form components.

### Prevention

Always verify namespace changes after framework upgrades.

### Files Affected

SubscriptionPlanMetadataForm

SubscriptionPlanInfolist

### Lessons Learned

Framework upgrades frequently move classes.

---

## KB-009 — Preview Component Missing Variables

### Context

Pricing preview failed to render.

### Exact Error

Undefined variable

plan

### Root Cause

Blade view expected variables never supplied by ViewField.

### Fix

Provide viewData explicitly.

### Why The Fix Works

Preview receives all required state.

### Prevention

Custom ViewFields require explicit view data.

### Files Affected

SubscriptionPlanMetadataForm

Preview Blade

### Lessons Learned

Blade templates should never assume implicit variables.

---

## KB-010 — Widget Lifecycle Misunderstanding

### Context

Initial implementation attempted to reuse a standalone Blade component as a Filament Widget.

### Root Cause

Widgets and Blade components have different lifecycles.

### Fix

Reimplemented preview using ViewField instead of Widget.

### Why The Fix Works

Preview updates with Livewire state automatically.

### Prevention

Use Widgets for dashboards.

Use ViewField for form previews.

### Files Affected

SubscriptionPlanPreview

SubscriptionPlanMetadataForm

### Lessons Learned

Choose framework primitives based on intended lifecycle.

---

## KB-011 — Live Preview Did Not Refresh

### Context

Metadata preview remained static.

### Root Cause

Preview received initial state only.

### Fix

Enabled live form updates and passed current form state using viewData.

### Why The Fix Works

Preview now reflects unsaved form changes.

### Prevention

Live previews should consume form state, not persisted models.

### Files Affected

SubscriptionPlanMetadataForm

Preview Blade

### Lessons Learned

Preview should represent pending edits.

---

# Dashboard

---

## KB-012 — Widgets Performing Business Logic

### Context

Early dashboard implementation risked placing calculations inside widgets.

### Root Cause

Widgets were becoming responsible for querying models.

### Fix

Introduced Service → Cache Service → DTO → Widget architecture.

### Why The Fix Works

Widgets remain presentation-only.

### Prevention

Never calculate business metrics inside presentation layers.

### Files Affected

Dashboard Services

Dashboard Widgets

Cache Services

### Related Architecture Decisions

ADR-003

### Lessons Learned

Presentation should never own business logic.

---

## KB-013 — Duplicate Dashboard Queries

### Context

Multiple widgets required identical data.

### Root Cause

Each widget risked querying independently.

### Fix

Centralized queries behind cache services.

### Why The Fix Works

Shared cached metrics reduce duplicate work.

### Prevention

Shared dashboards should aggregate data once.

### Files Affected

Dashboard Cache Services

Widgets

### Lessons Learned

Cache aggregated metrics rather than individual widget results.

---

## KB-014 — Health Logic Embedded In Tables

### Context

Organization Health initially appeared to belong inside the table widget.

### Root Cause

Health calculations and rendering became coupled.

### Fix

Created Health Service, DTO and Enum.

### Why The Fix Works

Health becomes reusable across reports, exports and APIs.

### Prevention

Treat health as a domain concept.

### Files Affected

OrganizationHealthService

Health DTO

Health Enum

Organization Health Table

### Lessons Learned

Tables display domain information.

They should not calculate it.

---

# Billing Lifecycle

---

## KB-015 — Backwards Lifecycle Transitions

### Context

Allowing plans to move back to Draft or Active after retirement would rewrite commercial history.

### Root Cause

CRUD state management ignored business lifecycle.

### Fix

Lifecycle restricted to forward-only transitions.

Draft → Active → Retired → Archived.

### Why The Fix Works

Commercial history remains trustworthy.

### Prevention

Model lifecycle before implementing state changes.

### Files Affected

SubscriptionPlanStatus

Lifecycle Actions

Policies

### Related Architecture Decisions

ADR-006

### Lessons Learned

State machines should reflect business reality rather than editing convenience.

---

# General Engineering Lessons

1. Business terminology matters as much as technical implementation.

2. Contracts should be immutable.

3. UI should never communicate directly with business logic.

4. DTOs are application boundaries.

5. Widgets render data; they do not calculate it.

6. Cache aggregated business information instead of individual UI fragments.

7. Scheduled lifecycle events are safer than immediate destructive actions.

8. Separate presentation metadata from commercial contracts.

9. Favor extending existing workflows over introducing parallel implementations.

10. Every architectural shortcut becomes future technical debt if accepted without challenge.



# Week 7 — Reporting & Platform Intelligence

---

## KB-016 — __PHP_Incomplete_Class Returned From Cache

### Context

Reporting DTOs retrieved from cache occasionally returned
`__PHP_Incomplete_Class`.

### Root Cause

Serialized DTOs were not being restored correctly by the cache driver.

### Fix

Introduced `BaseReportingCacheService` to centralize cache retrieval,
detect corrupted cache entries, clear them automatically and regenerate
fresh data.

### Why The Fix Works

Every reporting cache now shares the same retrieval and recovery logic.

### Prevention

Never duplicate cache retrieval logic. Shared infrastructure should own
cache validation and recovery.

### Files Affected

- BaseReportingCacheService
- ProjectReportingCacheService
- TeamReportingCacheService
- OrganizationReportingCacheService

### Lessons Learned

Cache infrastructure belongs in one reusable abstraction.

---

## KB-017 — Reporting Widgets Should Not Consume DTO Objects Directly

### Context

Filament `TableWidget` failed when provided with reporting DTO collections.

### Root Cause

Table widgets expect Eloquent models or arrays rather than DTO objects.

### Fix

Converted reporting DTO collections into arrays before supplying them to
the widget.

### Why The Fix Works

Widgets receive data in a format Filament can render while reporting
services remain DTO-based.

### Prevention

Keep DTOs inside the service layer. Convert them at the presentation
boundary when required.

### Files Affected

- ProjectHealthTableWidget
- TeamProductivityTableWidget

### Lessons Learned

Presentation boundaries often require transformation even when the
application layer uses DTOs.

---

## KB-018 — Team Productivity Relationship Was Incorrect

### Context

Initial productivity calculations attempted to count tasks directly from
teams.

### Root Cause

Tasks belong to Projects, not Teams.

### Fix

Refactored productivity calculations to traverse:

Team → Projects → Tasks

### Why The Fix Works

The reporting layer now follows the actual domain model.

### Prevention

Reporting should aggregate existing relationships rather than invent new
ones for convenience.

### Files Affected

- TeamReportingService

### Lessons Learned

Reporting should mirror the domain hierarchy instead of bypassing it.

---

## KB-019 — Retired Plans Could Still Renew

### Context

Renewal jobs continued processing subscriptions whose plans had already
been retired.

### Root Cause

Renewal logic only checked subscription expiry and ignored plan status.

### Fix

Added retirement validation before renewal while preserving existing
customer agreements until their scheduled retirement date.

### Why The Fix Works

Existing subscribers continue uninterrupted while retired plans cannot
receive future renewals.

### Prevention

Lifecycle rules should always validate both the subscription and the
commercial product it references.

### Files Affected

- RenewSubscriptionService
- Subscription
- RenewSubscriptionsService

### Lessons Learned

Commercial lifecycle rules should be enforced at every entry point, not
only during administration.


# Week 8 Errors

## Mistake

Task creation implemented file uploads.

### Problem

Tasks began owning files.

### Solution

Removed upload functionality.

Tasks now reference existing project attachments.

---

## Mistake

Incorrect eager-loading relationship.

```php
attachment.storedFile

ERR-031 — storage_used_bytes vs current storage column inconsistency

Problem

Different parts of the application referenced different storage column names. The old architecture used storage_used_bytes, while the current schema had been changed.

Impact

Usage queries and infrastructure metrics could reference a column that no longer represented the canonical schema.

Solution

Standardized usage code around the current canonical storage column.

Lesson

When refactoring schema terminology, search the entire application for the old name rather than fixing only the immediate failing query.

ERR-032 — Storage metrics duplicated organization health

Problem

InfrastructureMetricsService contained storage metrics such as:

Storage Used
Storage Limit
Storage Usage Percentage

while organization health already calculated actual organization-level storage usage and limits.

Solution

Removed the conceptual duplication.

Infrastructure metrics now represent platform-level infrastructure, not individual organization quotas.

Lesson

A metric's location should be determined by the question it answers, not merely by the fact that the same underlying number is available.

ERR-033 — Confusing plan storage with organization storage

Problem

Storage fields existed in both plan and organization-related structures, creating ambiguity about whether a field represented:

allowed storage
consumed storage
infrastructure capacity

Solution

Established:

Plan.max_storage_mb
    = allowed customer capacity

OrganizationUsage.storage_used
    = actual customer consumption

Infrastructure capacity remains a separate concern.

Lesson

Names and ownership must make the meaning of a metric obvious.

ERR-034 — Attempting to calculate organization-wide task counts through invalid relationship traversal

Problem

The organization does not have a direct task relationship.

A chain such as:

$organization->projects()->tasks()

is invalid because projects() returns a relationship to Project; it does not magically expose the Task relationship.

Solution

Use relationships that actually exist or define an explicit HasManyThrough only where the domain/schema supports it.

For current TaskForge architecture, project is the direct owner of tasks.

Lesson

Laravel relationship methods represent actual domain relationships. They are not arbitrary query namespaces.

ERR-035 — Storage quota duplicated entity quota enforcement

Problem

There was a risk of making OrganizationUsageService responsible for checking every entity limit even though policies already enforce those limits.

Solution

Kept entity authorization in policies and used usage tracking primarily for accounting/reconciliation, with storage receiving explicit quota enforcement.

Lesson

Do not create a second authorization system simply because usage data is available.

ERR-037 — Laravel Boost provider was referenced but unavailable
Problem

The application, Horizon, and Scheduler failed with:

Class "Laravel\Boost\BoostServiceProvider" not found
Cause

Laravel package discovery metadata referenced a provider that was not available inside the production dependency set.

The container was built with production dependencies only.

composer install --no-dev
Solution

Corrected the package discovery/cache mismatch so Laravel metadata matched the packages actually installed inside the image.

Lesson

Cached framework metadata must match the dependency set available at runtime.

ERR-038 — php artisan package:discover failed during image build
Problem

Running:

php artisan package:discover --ansi

during the Docker build caused an application configuration failure.

Cause

The application attempted to boot using configuration that depended on runtime environment values.

Solution

Separated image construction from environment-dependent application bootstrapping.

Lesson

The Docker build environment is not automatically a valid Laravel runtime environment.

ERR-039 — Bind mount caused missing vendor/autoload.php
Problem

The application containers entered a restart loop.

The error was:

Failed opening required '/var/www/html/vendor/autoload.php'
Cause

The bind mount:

- .:/var/www/html

replaced the application directory packaged into the image.

The mounted project directory did not yet contain the vendor directory.

Solution

Installed Composer dependencies into the mounted project before starting the services.

docker compose run --rm app composer install
docker compose up -d
Lesson

A bind mount can hide files that already exist inside the image.

ERR-040 — Windows Node.js was executed from WSL
Problem

Node and Vite operations produced path and UNC-related failures.

Cause

WSL was resolving the Windows Node.js installation instead of a Linux-native installation.

Windows executables do not reliably operate against WSL Linux filesystem paths.

Solution

Installed Node.js inside Ubuntu using NVM.

nvm install --lts

Then recreated the Node dependencies.

rm -rf node_modules
npm install
Lesson

Inside WSL, use Linux-native development tools.

ERR-041 — Redis connection failed using 127.0.0.1
Problem

Laravel failed to connect to Redis.

Connection refused [tcp://127.0.0.1:6379]
Cause

Redis was running inside a separate Docker container.

Inside the Laravel container, 127.0.0.1 referred only to the Laravel container itself.

Solution

Configured Redis using the Docker Compose service name.

REDIS_HOST=redis
Lesson

Containers communicate through Docker networking and service names.

ERR-042 — Notification relationship traversal was invalid
Problem

A notification operation failed because the relationship traversal did not match the actual TaskForge domain relationships.

Cause

The logic attempted to traverse relationships through a path that Laravel did not define.

Solution

Corrected the traversal to use the actual relationship structure.

Lesson

Eloquent relationship methods represent explicit domain relationships.

They cannot be chained arbitrarily.

ERR-043 — Notification URLs pointed to the wrong environment
Problem

Stored notification URLs pointed to:

http://taskforge.test

while the application was running on:

http://localhost:8000
Cause

Environment-specific absolute URLs were stored in notification data.

Solution

Updated:

APP_URL=http://localhost:8000

The notification redirect logic was adjusted to normalize redirects to relative paths.

Future routes should use relative URLs where appropriate.

route('route.name', [], false)
Lesson

Persistent application data should not depend on a specific local development domain.