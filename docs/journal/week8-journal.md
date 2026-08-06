# Week 8 — Attachments & Storage

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

# Week 8 — Attachments & Storage

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
