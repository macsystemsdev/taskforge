# Week 1 — Foundational Domain Architecture

## Weekly Objective

Establish the foundational architecture of TaskForge.

Focus areas:
- relationship modeling,
- organization ownership,
- membership lifecycle,
- invitation workflows,
- service layer introduction,
- Livewire architecture,
- action-based business workflows.

Goal:
Build the application's core collaboration domain correctly before scaling features.

---

# Day 1 — Relationship Modeling & Membership Architecture

## Objective

Design the foundational organization and membership architecture.

Focused on:
- organization ownership,
- multi-user collaboration,
- relationship types,
- invitation preparation,
- pivot modeling.

---

# Features Implemented

## Organizations

Created:
- Organization model,
- organization migration,
- ownership relationship.

Organizations now support:
- creator ownership,
- multi-user collaboration,
- future tenant isolation.

---

## Membership System

Implemented many-to-many relationship between:
- users,
- organizations.

Created:
- organization_user pivot table,
- OrganizationUser pivot model.

Pivot fields:
- role,
- joined_at,
- invited_by,
- status.

---

# Relationships Defined

## User → Organizations

Relationship:
Many-to-many.

Reason:
A user may belong to multiple organizations.

---

## Organization → Members

Relationship:
Many-to-many.

Reason:
Organizations contain multiple collaborating users.

---

## User → Owned Organizations

Relationship:
One-to-many.

Reason:
A user may create multiple organizations.

Used:
owner_id foreign key on organizations table.

---

# Architectural Decisions

## Why Invitations Were Separated From Memberships

Did not use:
organization_user table for pending invitations.

Reason:
Membership and invitation represent different lifecycle states.

Membership:
- active access.

Invitation:
- pending access negotiation.

Created dedicated invitation domain instead.

---

## Why Polymorphic Relationships Were Avoided

Did not use polymorphic many-to-many relationships.

Reason:
- organizations and teams do not share interchangeable ownership behavior,
- relationship responsibilities differ,
- explicit modeling keeps architecture simpler and clearer.

Used explicit relationship definitions instead.

---

# Problems Encountered

## Incorrect Pivot Relationships

Initially attempted:
belongsToMany relationships inside pivot model.

Issue:
Pivot models represent intermediate records,
not relationship roots.

Resolved by:
using belongsTo relationships inside pivot model.

---

## Relationship Responsibility Confusion

Initially mixed:
- ownership,
- membership,
- invitations.

Resolved by separating:
- owner relationship,
- membership relationship,
- invitation lifecycle.

---

# Key Engineering Lessons

- Relationship type should reflect domain behavior.
- Invitations are not memberships.
- Pivot tables should remain lifecycle-specific.
- Ownership and membership are different responsibilities.
- Polymorphic relationships should only be used when entities truly share interchangeable behavior.

---

# Day 2 — Service Layer & Livewire Foundation

## Objective

Move organization workflows into proper orchestration layers.

Focused on:
- service layer introduction,
- Livewire integration,
- organization creation flow,
- frontend rendering,
- application layout architecture.

---

# Features Implemented

## OrganizationService

Created:
OrganizationService.

Responsibilities:
- organization creation,
- owner attachment,
- membership initialization.

---

## WorkspaceService

Created:
WorkspaceService.

Prepared for:
- workspace lifecycle orchestration,
- future team/project grouping.

---

## Livewire Organization Creation Flow

Built:
- organization creation page,
- form submission flow,
- database persistence,
- redirects.

Using:
- Livewire,
- Volt,
- Flux UI components.

---

## Application Layout System

Created reusable:
x-layouts::app layout.

Integrated:
- sidebar,
- header,
- application shell structure.

---

# Architectural Decisions

## Why Service Layer Was Introduced

Avoided:
- fat controllers,
- route-based business logic.

Services now coordinate:
- reusable workflows,
- organization lifecycle operations.

---

## Why Livewire Components Were Kept Thin

Avoided placing:
- membership logic,
- invitation workflows,
- organization orchestration

inside components.

Components focus on:
- UI state,
- validation,
- interaction.

Business workflows moved into:
- services,
- actions.

---

# Problems Encountered

## Volt View Resolution Failures

Problem:
Livewire attempted loading views from incorrect directories.

Resolved by:
moving views into proper Livewire structure.

---

## Layout Rendering Failures

Encountered:
- undefined slot errors,
- missing layout wrapper,
- component resolution failures.

Resolved by:
creating dedicated application layout structure.

---

## Incorrect Form Submission Method

Problem:
Organization form submitted as GET request.

Cause:
incorrect form handling.

Resolved by:
using proper wire:submit handling.

---

## Pivot Runtime Failure

Error:
fromRawAttributes() failure.

Cause:
incorrect pivot model relationship configuration.

Resolved by:
using belongsTo relationships correctly inside pivot model.

---

# Key Engineering Lessons

- Controllers should orchestrate, not contain business workflows.
- Livewire components should remain UI-focused.
- Service layers improve separation of concerns.
- Layout architecture affects entire application stability.
- Pivot models are specialized records, not standard domain models.

---

# Day 3 — Invitation Lifecycle System

## Objective

Build production-style organization invitation workflows.

Focused on:
- invitation creation,
- email workflows,
- acceptance/rejection flows,
- expiration handling,
- invitation state tracking.

---

# Features Implemented

## Invitation System

Created:
- invitations table,
- Invitation model,
- invitation token system.

Implemented:
- invitation email flow,
- pending invitations,
- expiration support.

---

## Invitation Acceptance Flow

Built:
- invitation acceptance route,
- membership attachment,
- invitation state transition.

Accepted invitations now:
- attach user to organization,
- activate membership,
- update invitation lifecycle status.

---

## Invitation Rejection Flow

Implemented:
- rejection page,
- rejection submission,
- optional rejection reason storage.

Rejected invitations now:
- preserve audit history,
- expose lifecycle visibility to sender.

---

## Invitation Status Tracking

Implemented statuses:
- pending,
- accepted,
- rejected,
- cancelled,
- expired.

---

## Duplicate Invitation Prevention

Prevented:
multiple pending invitations for same:
- organization,
- email combination.

---

# Architectural Decisions

## Why Invitations Were Given Dedicated Lifecycle States

Invitation state transitions now behave independently from:
- membership state,
- authentication state.

This improves:
- auditability,
- workflow clarity,
- lifecycle visibility.

---

## Why Invitation Views Were Centralized

Decided to create dedicated invitation management views instead of:
embedding everything directly inside organization page.

Reason:
Invitation workflows now have enough complexity to justify dedicated visibility.

---

# Problems Encountered

## Duplicate Membership Insertions

Problem:
users accepting invitations after registration triggered unique constraint violations.

Cause:
membership already existed.

Resolved by:
checking existing membership before attaching.

---

## Validation Errors Not Rendering

Problem:
ValidationException messages did not appear on frontend.

Cause:
Livewire validation lifecycle was bypassed.

Resolved by:
keeping validation inside Livewire execution flow and rendering explicit error messages.

---

## Invitation Expiration Errors

Problem:
isPast() called on string.

Cause:
expires_at not properly cast.

Resolved by:
adding datetime casting on model.

---

# Key Engineering Lessons

- Invitation lifecycle is separate from active membership lifecycle.
- Lifecycle visibility matters for collaboration systems.
- State transitions should be explicit and trackable.
- Validation success on backend does not guarantee frontend UX success.
- Pending collaboration systems require audit-friendly workflows.

---

# Day 4 — Action-Based Workflow Architecture

## Objective

Refactor invitation workflows into dedicated business actions.

Focused on:
- action-based architecture,
- controller orchestration,
- authentication redirect lifecycle,
- workflow separation.

---

# Features Implemented

## Invitation Actions

Created:
- InviteMemberAction,
- AcceptInvitationAction,
- RejectInvitationAction,
- CancelInvitationAction.

Each action now encapsulates:
- workflow rules,
- lifecycle transitions,
- authorization checks,
- membership coordination.

---

## Thin Controller Refactor

Refactored controllers to:
- orchestrate requests,
- delegate workflows to actions,
- return responses only.

Removed:
- heavy business logic,
- direct membership orchestration,
- lifecycle mutations from controllers.

---

## Authentication Redirect Lifecycle

Implemented:
proper intended redirect behavior for invitation routes.

Unauthenticated users now:
- login first,
- return to invitation flow automatically.

---

# Architectural Decisions

## Why Actions Were Introduced

Invitation workflows now contain:
- state transitions,
- authorization,
- lifecycle protection,
- reusable business logic.

Actions provide:
- clear domain boundaries,
- reusable workflows,
- cleaner orchestration layers.

---

## Why Actions Were Not Used Everywhere

Did not create actions for:
- simple view rendering,
- passive queries,
- trivial operations.

Avoided over-engineering and abstraction bloat.

---

# Problems Encountered

## Livewire Validation Lifecycle Breakage

Problem:
moving invitation logic into controllers bypassed Livewire validation propagation.

Resolved by:
keeping UI validation flow inside Livewire layer while delegating business workflows into actions.

---

## Incorrect Authentication Redirect Flow

Problem:
users redirected to dashboard instead of invitation flow after login.

Cause:
hardcoded redirects overriding intended session redirects.

Resolved by:
using redirect()->intended() lifecycle correctly.

---

# Architectural Mistakes Corrected

- Incorrectly placed business logic directly inside routes.
- Initially attempted using belongsToMany inside pivot model.
- Mixed invitation lifecycle with active membership lifecycle.
- Broke Livewire validation lifecycle by shifting execution context into controllers.
- Initially treated invitation workflows as simple CRUD instead of lifecycle-driven processes.

---

# Weekly Engineering Lessons

- Lifecycle modeling is more important than CRUD generation.
- Relationships should reflect real business behavior.
- Thin orchestration layers improve maintainability.
- Invitation systems are workflow engines, not simple database inserts.
- State transitions should remain explicit and observable.
- Correct abstraction boundaries matter more than maximum abstraction.

---

# Weekly Deliverables

Completed:
- organization architecture,
- membership system,
- invitation lifecycle,
- action-based workflows,
- Livewire frontend integration,
- application layout structure,
- authentication redirect handling.

TaskForge now supports:
- organization creation,
- member invitations,
- invitation acceptance/rejection,
- lifecycle tracking,
- active membership management.

---

# Next Week Plan

Focus areas:
- projects,
- tasks,
- advanced Eloquent relationships,
- polymorphic comments,
- activity feeds,
- query optimization,
- scopes,
- eager loading,
- indexing strategies.