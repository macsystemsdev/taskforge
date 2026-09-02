# Week 3 — Attachments & Storage

## Overview

This week introduced TaskForge's storage architecture and the first collaboration features built on top of it. Rather than treating file uploads as isolated functionality, storage was designed as reusable infrastructure capable of supporting project attachments, workspace libraries, organization libraries, avatars, logos, voice notes, and future document management.

The project discussion experience was also transformed into a richer collaboration space with attachment support while preserving the existing project-centric workflow.

---

## Day 1 — Storage Architecture

### Planned Goal

Design a reusable storage architecture that supports the entire platform.

### Completed

* Designed the complete storage domain
* Introduced `StoredFile` as centralized file metadata
* Introduced polymorphic `FileAttachment`
* Defined storage lifecycle
* Created storage permissions
* Added storage policies
* Implemented storage enums
* Designed storage directory strategy
* Added `StoragePath` service
* Implemented `FileStorageService`
* Added reusable upload validation rules
* Built `ValidateIncomingFileService`
* Added storage security extension points
* Defined organization, workspace and project library architecture

### Lesson Learned

Storage should be treated as platform infrastructure rather than a feature. Designing it around reusable services avoids duplicating upload logic across multiple domains.

### Visible Result

TaskForge now has a unified storage foundation capable of supporting every future upload feature.

---

# Week 3 — Attachments & Storage

---

## Day 2 — Project Attachments

### Planned Goal

Implement project attachment management as the collaborative file layer for TaskForge.

### Completed

- Built centralized file upload pipeline.
- Implemented project discussion attachments.
- Introduced FileStorageService.
- Added ValidateIncomingFileService.
- Added UploadStoredFileAction.
- Added download, delete and storage utilities.
- Implemented attachment activity logging.
- Added project attachment browser.
- Added attachment previews.
- Added drag-and-drop uploads.
- Added project file search and filtering.
- Improved project discussion UI.

### Lesson Learned

Files should be uploaded once and referenced everywhere else. Centralizing storage eliminates duplication and simplifies lifecycle management.

### Visible Result

Projects now support collaborative file sharing through the discussion area.

---

## Day 3 — Task Resources

### Planned Goal

Allow tasks to reference project resources without creating duplicate files.

### Completed

- Designed TaskFileReference model.
- Added task resource relationships.
- Implemented AttachTaskResourceAction.
- Extended CreateTaskAction to attach resources.
- Added TaskResourceService.
- Added resource picker to task creation.
- Added referenced resources to task details.
- Added task priority support.
- Extended task lifecycle with execution metadata.
- Planted architecture for future blocker history.

### Lesson Learned

Tasks should organize work, not own files. Referencing existing project resources keeps storage centralized while providing assignees with the exact material required.

### Visible Result

Task leaders can select project resources during task creation, and assignees immediately know which project files are relevant.


## Day 4 & 5 — Billing, Usage & Infrastructure Boundaries
# Objective

Review the billing/storage architecture after implementing organization usage tracking and storage quotas, identify duplicated responsibilities, and establish clean boundaries between billing, usage, quota enforcement, and infrastructure metrics.

# Work completed
- Reviewed the original Day 5 billing-integration requirement against the current billing architecture.
- Determined that most entity billing limits already existed through plan metadata and authorization policies.
- Avoided duplicating project/task/member/team/workspace quota logic inside the usage system.
- Established storage as the primary resource requiring continuous usage accounting.
- Added/confirmed organization storage usage tracking.
- Added storage quota enforcement using the organization's active plan.
- Added a dedicated quota exception instead of scattering generic validation errors throughout upload flows.
- Added usage increment/decrement operations.
- Added usage reconciliation through the existing usage:recalculate Artisan command.
- Verified plan storage configuration through max_storage_mb.
- Cleaned up inconsistencies between old storage column names and the current schema.
- Reviewed infrastructure metrics and rejected duplication of organization-level storage metrics.
- Defined platform-wide storage consumption as an infrastructure metric rather than another organization-health metric.
- Preserved the distinction between application-managed storage and actual infrastructure/DigitalOcean storage.

Final architecture
SubscriptionPlan
    │
    └── Defines allowed limits
            │
            ▼
      Quota / Policies
            │
            └── Enforces limits


OrganizationUsage
    │
    └── Records actual consumption
            │
            ├── Entity metrics
            └── Storage usage


OrganizationHealth
    │
    └── Customer-level health and quota information


InfrastructureMetrics
    │
    └── Platform-wide infrastructure consumption
Storage model
Plan
    max_storage_mb
         │
         │ allowed
         ▼
Organization
    OrganizationUsage
         │
         │ actual usage
         ▼
    storage_used

Storage usage remains internally represented in bytes. Conversion to MB/GB belongs at the presentation boundary.

Reconciliation model

Normal operations use incremental counters:

Create resource
    → increase usage

Delete resource
    → decrease usage

The system also has an authoritative reconciliation path:

Actual database state
        ↓
usage:recalculate
        ↓
OrganizationUsage corrected

This gives us cheap reads during normal operation without sacrificing the ability to repair inaccurate counters.

Storage quota principle
current usage + incoming file size
            ↓
       quota check
            ↓
       allow / reject

A quota violation produces a dedicated exception.

Existing data is not deleted simply because a customer exceeds a newly reduced quota.