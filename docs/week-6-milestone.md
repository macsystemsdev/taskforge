# TaskForge Week 6 Milestone Summary

This document captures all major work completed since the previous documentation (Pulse setup and Day 1 operational baseline).

---

## 1. Pulse Stabilization

### Fixed
- MySQL 9.7 incompatible Pulse migration: replaced generated `key_hash` column with app-managed `string('key_hash', 32)->nullable()`.
- Redis serializer issue: switched from `predis` to `phpredis` and added `'serializer' => 1` in `config/database.php`.
- Recurring `JsonException` in Pulse cards caused by null `key_hash`: created `app/Support/Pulse/MySqlPulseStorage.php` to force manual key hash for MySQL.
- Cleaned all Pulse tables and Redis cache multiple times to eliminate stale malformed data.
- Re-enabled Exceptions card after confirming stability.

### Current state
- Pulse dashboard loads cleanly.
- Slow Requests, Slow Queries, Usage, Queues, Cache all populate correctly.
- Exceptions card functional.

---

## 2. Performance Optimization

### Filament Reporting page
- Identified N+1 query pattern in `ProjectHealthService` and `TeamProductivityService`.
- Added guards to use eager-loaded count attributes (`tasks_count`, `completed_tasks_count`, `blocked_tasks_count`, etc.) before issuing new queries.
- Result:
  - Project Health cold cache: **157 ms / 1 query** (was ~7,400 ms / hundreds of queries).
  - Team Productivity cold cache: **118 ms / 1 query**.

### Public `/reports` page
- Optimized stats to use two aggregate SQL queries instead of many individual counts.
- Added computed completion rates based on the same stats.

---

## 3. Authorization & Locking

- Added `LockedResourceException` and friendly `errors.locked` page.
- Added route-level lock checks for projects, tasks, teams, and workspaces.
- Added null-team guards across `TaskPolicy`, `ProjectPolicy`, and `Organization::teamLocked`.
- Removed unused duplicate limit/locked exceptions.
- Implemented filtering for project/task index pages to avoid unauthorized 403s on click.
- Added lock badges with SVG icons consistently on workspace, team, project, and task index views.
- Added "Upgrade Plan" buttons when feature limits are reached.

---

## 4. Dashboard & Reporting UI

### Dashboard
- Fixed total organization count to reflect all active organizations, not just displayed 5.
- Added Due Soon pagination and View All link.

### Reporting page
- Restructured into Projects/Tasks sections with clear labels.
- Added Projects Created/Completed (period) and Tasks Created/Completed (period).
- Added Overdue/Due Soon snapshot metrics for both projects and tasks.
- Added project/task completion rates.
- Added top 10 projects by task volume with explicit CTA.
- Fixed spacing with inline styles to ensure clean vertical rhythm.

---

## 5. Subscription Plans & Currency

### Admin plan creation
- Replaced free-text currency input with `Select` using `SupportedCurrency` enum.
- Dynamic price prefix based on selected currency.
- Created `app/Support/CurrencyFormatter` and `app/Domain/Billing/Enums/SupportedCurrency`.

### Plan model
- `SubscriptionPlan::formattedPrice()` now uses `CurrencyFormatter` instead of hardcoded `$`.
- Supports USD, EUR, GBP, and XAF (fallback symbol).

### Billing page
- Plan cards now show `Billed in {currency}` under price.
- Organization cards show dynamic plan name from actual subscription.

---

## 6. Trial Extension

- Added `trial_extended` boolean to `subscriptions` via migration.
- Updated `Subscription` model fillable/casts.
- `ExtendTrialAction` now throws `DomainException` if already extended.
- Organization Health table hides "Extend Trial" button after one extension.
- Admin sees clear read-only subscription modal instead of navigating to organization billing as owner.

---

## 7. Organization Health Table Fixes

- Fixed subscription action 404 by using organization slug instead of ID.
- Converted subscription action from redirect to modal displaying:
  - Current plan
  - Status with color badge
  - Start/end/trial dates
  - Pending plan if present
- Added plan/health filters to Organization Health table.
- Implemented sorting on collection-based tables (Project Health, Team Productivity, Organization Health).

---

## 8. Demo Data & Seeders

- Updated `SubscriptionPlanSeeder` with realistic limits.
- Rewrote `DemoDataSeeder` to use real enums and create meaningful organizations, subscriptions, workspaces, teams, projects, tasks, and payments.
- Added `DemoDataSeeder` to `DatabaseSeeder`.
- Fixed orphan project references.

---

## 9. Currency Strategy (Partial)

- Established foundation with `SupportedCurrency` and `CurrencyFormatter`.
- Future work:
  - User-specific display currency conversion.
  - MTN/Orange Mobile Money integration with XAF amounts.
  - Stripe checkout conversion as needed.

---

## 10. Known Next Steps

- Finish billing page plan presentation using metadata.
- Implement MTN/Orange checkout.
- Revisit skipped tests and CI failures.
- Investigate Livewire checksum failures seen in Pulse cache misses.

---

*This document is for internal TaskForge tracking and should be updated as new work lands.*
