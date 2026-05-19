User
 ├── owns Organizations
 ├── belongs to Organizations
 ├── belongs to Teams
 ├── creates Tasks
 ├── assigned Tasks
 ├── writes Comments
 └── receives Notifications

Organization
 ├── contains Workspaces
 ├── contains Teams
 ├── has Members
 └── owns Subscription

Workspace
 └── contains Projects

Project
 ├── contains Tasks
 ├── shared with Teams
 ├── receives Comments
 ├── receives Attachments
 └── generates Activity Logs

Task
 ├── assigned to User
 ├── receives Comments
 ├── receives Attachments
 └── generates Activity Logs