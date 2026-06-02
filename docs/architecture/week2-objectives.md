# Week 2 — Projects and Tasks Enter the System

## Overview

This week focused on introducing the operational layer of the system. Organizations moved beyond membership management and became capable of executing work through projects and tasks. Collaboration, visibility, and performance considerations also began entering the architecture.

---

## Day 1 — Projects

### Planned Goal

Create Project model and workspace relationships.

### Completed

* Created Project model and migrations
* Added Workspace → Project relationships
* Implemented project creation workflow
* Introduced project DTOs and Actions
* Added slug-based routing
* Built project create and show pages

### Lesson Learned

Projects are operational units that exist within workspaces. Contextual domain modeling simplified relationship design and routing considerably.

### Visible Result

Organizations can create and manage projects.

---

## Day 2 — Tasks

### Planned Goal

Create Task model, statuses, priorities, and assignments.

### Completed

* Created Task model
* Added status workflow
* Added priority system
* Implemented task assignment
* Added task DTOs and Actions
* Created task create and show pages
* Added task notifications
* Added slug routing

### Lesson Learned

Tasks are where work actually happens. Once tasks entered the system, relationships and ownership boundaries became much more important.

### Visible Result

Projects became operational through task execution.

---

## Day 3 — Comments

### Planned Goal

Implement collaboration through comments.

### Completed

* Created polymorphic comment architecture
* Added comments to tasks
* Added comments to projects
* Linked comments to authenticated users
* Implemented comment display and submission

### Lesson Learned

Polymorphic relationships eliminate duplication when the same collaboration feature must exist across multiple domain entities.

### Visible Result

Users can collaborate directly inside projects and tasks.

---

## Day 4 — Activity Tracking

### Planned Goal

Track project and task events using ActivityLogs.

### Completed

* Created ActivityLog model
* Implemented polymorphic activity tracking
* Created CreateActivityLogAction
* Logged project creation
* Logged task creation
* Logged task assignment
* Logged invitation lifecycle events

### Lesson Learned

Activity systems should capture business events rather than database mutations. Meaningful events create useful operational history.

### Visible Result

The application now provides operational visibility.

---

## Day 5 — Performance Review

### Planned Goal

Fix N+1 issues, add indexes, and review eager loading.

### Completed

* Installed Laravel Telescope
* Audited request performance
* Investigated N+1 query risks
* Reviewed eager loading strategy
* Added database indexes
* Analyzed slow requests

### Lesson Learned

Most performance problems originate from inefficient database access patterns rather than infrastructure limitations.

### Visible Result

The application is better prepared for increasing complexity.

---

## Week 2 Reflection

Week 2 marked the transition from organizational structure into operational workflows.

The system gained:

* Projects
* Tasks
* Notifications
* Comments
* Activity Tracking
* Performance Monitoring

The most important architectural themes this week were contextual domain modeling, reusable polymorphic relationships, business-event-driven activity tracking, and disciplined database access patterns.

The platform now supports both collaboration and operational visibility, providing a strong foundation for workflow execution in future iterations.
