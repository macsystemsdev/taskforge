# Week 3 — Collaboration and Authorization

## Overview

This week focused on introducing structured collaboration and authorization across the platform. Teams became first-class domain entities, project collaboration expanded beyond individual ownership, and the foundation for role-based access control was established.

Several architectural assumptions were challenged during implementation, resulting in important domain refinements around workspaces, teams, projects, and permissions.

---

## Day 1 — Teams

### Planned Goal

Create a dedicated team system independent of the Laravel starter kit.

### Completed

* Removed dependence on starter-kit personal teams
* Refactored team architecture
* Created Team model and relationships
* Implemented team creation workflow
* Implemented team ownership
* Added team memberships
* Added team invitations
* Built team CRUD functionality

### Lesson Learned

Starter-kit abstractions are useful for rapid development but often become constraints when the domain begins to diverge from their assumptions.

### Visible Result

Organizations can create and manage collaborative teams.

---

## Day 2 — Project-Team Collaboration

### Planned Goal

Allow teams to collaborate on projects.

### Completed

* Implemented many-to-many Project ↔ Team relationship
* Created dedicated pivot model extending Pivot
* Added team assignment to projects
* Added project visibility within teams
* Added team membership activity logging
* Implemented sync-based relationship management

### Lesson Learned

Many-to-many relationships are state management problems. The goal is to persist the current state accurately rather than track every intermediate action.

### Visible Result

Projects can now be shared across multiple teams.

---

## Day 3 — Organization RBAC

### Planned Goal

Introduce role-based access control at the organization level.

### Completed

* Created OrganizationRole enum
    * owner
    * admin
    * member
* Added role storage to OrganizationUser pivot
* Implemented role assignment during invitation acceptance
* Added role update functionality
* Created UpdateOrganizationMemberRoleAction
* Added enum casting support
* Displayed member roles within organization pages
* Logged membership and role changes

### Lesson Learned

Roles belong to memberships rather than users. A user's authority depends on context, not identity.

### Visible Result

Organizations can manage member permissions through roles.

---

## Day 4 — Authorization Policies

### Planned Goal

Build middleware, gates, and feature restrictions.

### Completed

* Created OrganizationPolicy
* Added organization authorization checks
* Implemented:
  * view()
  * update()
  * delete()
  * inviteMembers()
  * removeMembers()
  * changeMemberRole()
  * createWorkspace()
  * createTeam()
  * createProject()
  * viewActivityLog()
* Added policy enforcement in Livewire components
* Added policy enforcement in Blade views
* Added Gate authorization throughout workflows
* Implemented UI-level feature visibility based on permissions

### Lesson Learned

Policies answer who may perform an action. Business rules determine whether the action should succeed. Mixing these responsibilities creates fragile systems.

### Visible Result

Role-based protection is enforced throughout organization workflows.

---

## Day 5 — Workspace Refactor

### Planned Goal

Review and refine workspace ownership boundaries.

### Completed

* Evaluated workspace, team, and project relationships
* Moved teams under workspaces
* Removed direct organization ownership from teams
* Updated team relationships
* Updated migrations
* Updated routing assumptions
* Updated workspace lifecycle design
* Added workspace creation workflow improvements
* Added workspace show page foundation
* Implemented slug-based workspace routing

### Lesson Learned

Early domain corrections are significantly cheaper than long-term architectural workarounds. When workflows and entity ownership disagree, the model should be corrected immediately.

### Visible Result

Workspaces now act as the operational container for projects and teams.

---

## Week 3 Reflection

Week 3 marked the transition from collaboration features into authorization and domain governance.

The system gained:

* Teams
* Team Memberships
* Team Invitations
* Project-Team Collaboration
* Organization Roles
* Organization Policies
* Authorization Enforcement
* Workspace Refactoring

The most important architectural themes this week were membership-based authorization, separation of permissions from business rules, many-to-many state management, and continuous domain refinement.

The platform now has the foundations required to support secure collaboration, controlled access, and scalable team workflows.