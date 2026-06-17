# Relationship Hierarchy

User
├── owns Organizations
├── belongs to Organizations
├── belongs to Teams
├── creates Tasks
├── assigned Tasks
├── writes Comments
├── performs Activity Logs
└── receives Notifications

Organization
├── contains Workspaces
├── has Members
└── owned by User

Workspace
├── belongs to Organization
└── contains Teams

Team
├── belongs to Workspace
├── has Members
├── has Leaders
└── contains Projects

Project
├── belongs to Team
├── contains Tasks
├── receives Comments
├── receives Attachments
└── generates Activity Logs

Task
├── belongs to Project
├── assigned to User
├── created by User
├── receives Comments
├── receives Attachments
└── generates Activity Logs

Comment
├── belongs to User
└── belongs to Commentable (Project | Task)

Attachment
└── belongs to Attachable (Project | Task)

Activity Log
├── belongs to User
└── belongs to Subject (Project | Task)

Notification
└── belongs to User

---

# Operational Hierarchy

Organization
└── Workspace
└── Team
└── Project
└── Task

---

# Ownership Hierarchy

Organization Owner
└── Workspace
└── Team Leader
└── Project
└── Task Assignee

---

# Communication Hierarchy

Organization
└── Workspace
└── Team
└── Project Comments
└── Task Comments

---

# Audit Hierarchy

User Action
└── Activity Log
└── Project | Task
