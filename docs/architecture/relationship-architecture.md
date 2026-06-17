# Current Relationship Architecture

## Core Domain Relationships

| Entity A     | Relationship Type       | Entity B                          | Why Relationship Exists                | Structure             |
| ------------ | ----------------------- | --------------------------------- | -------------------------------------- | --------------------- |
| User         | One-to-Many             | Organization (ownedOrganizations) | User creates and owns organizations    | `owner_id`            |
| User         | Many-to-Many            | Organization                      | Users participate inside organizations | `organization_user`   |
| Organization | One-to-Many             | Workspace                         | Organizations contain workspaces       | direct FK             |
| Workspace    | One-to-Many             | Team                              | Workspaces contain teams               | direct FK             |
| Team         | One-to-Many             | Project                           | Teams own projects                     | direct FK             |
| Project      | One-to-Many             | Task                              | Projects contain tasks                 | direct FK             |
| User         | One-to-Many             | Task (creator)                    | User creates tasks                     | `creator_id`          |
| User         | One-to-Many             | Task (assignee)                   | User is responsible for execution      | `assignee_id`         |
| User         | One-to-Many             | Comment                           | User authors comments                  | direct FK             |
| User         | One-to-Many             | Notification                      | Notifications belong to users          | direct FK             |
| User         | One-to-Many             | ActivityLog                       | User performs actions                  | `user_id`             |
| Task         | Polymorphic One-to-Many | Comment                           | Tasks support discussions              | `commentable_type/id` |
| Project      | Polymorphic One-to-Many | Comment                           | Projects support discussions           | `commentable_type/id` |
| Task         | Polymorphic One-to-Many | ActivityLog                       | Tasks generate activity history        | `subject_type/id`     |
| Project      | Polymorphic One-to-Many | ActivityLog                       | Projects generate activity history     | `subject_type/id`     |
| Task         | Polymorphic One-to-Many | Attachment                        | Tasks can contain files                | `attachable_type/id`  |
| Project      | Polymorphic One-to-Many | Attachment                        | Projects can contain files             | `attachable_type/id`  |

---

# Membership Relationships

## Organization Membership

Users participate in organizations through memberships.

### organization_user

| Column          |
| --------------- |
| organization_id |
| user_id         |
| role            |
| joined_at       |

---

## Team Membership

Users participate in teams through memberships.

Leadership is determined from membership records rather than the Team model itself.

### team_user

| Column    |
| --------- |
| team_id   |
| user_id   |
| role      |
| joined_at |

Possible roles:

* Leader
* Member

---

# Hierarchy

Organization
└── Workspace
└── Team
└── Project
└── Task

---

# Ownership Flow

Organization Owner
↓
Workspace
↓
Team Leader
↓
Project
↓
Task Assignee

---

# Authorization Flow

## Organization Level

Can:

* Manage organization
* Manage workspaces
* Manage teams
* Override project administration
* Override task administration

## Team Level

Can:

* Manage team members
* Manage projects
* Reassign tasks
* Cancel tasks

## Task Level

Assignees can:

* Start tasks
* Complete tasks

---

# Future Relationships (Not Yet Implemented)

These entities are planned but not yet active in the production domain model:

## Billing

Organization
└── Subscription

SubscriptionPlan
└── Subscription

## Reporting

Organization
└── Reports

## Notifications

User
└── Notification

## Audit Expansion

Additional activity subjects may be introduced beyond:

* Projects
* Tasks
