| Entity A         | Relationship Type       | Entity B                          | Why Relationship Exists                  | Pivot / Special Structure   |
| ---------------- | ----------------------- | --------------------------------- | ---------------------------------------- | --------------------------- |
| User             | One-to-Many             | Organization (ownedOrganizations) | User creates/owns organizations          | `owner_id` on organizations |
| User             | Many-to-Many            | Organization (memberships)        | Users participate inside organizations   | `organization_user`         |
| Organization     | One-to-Many             | Workspace                         | Organizations contain operational spaces | direct FK                   |
| Organization     | One-to-Many             | Team                              | Organizations contain teams/departments  | direct FK                   |
| User             | Many-to-Many            | Team                              | Users belong to organizational subgroups | `team_user`                 |
| Workspace        | One-to-Many             | Project                           | Projects belong to operational spaces    | direct FK                   |
| Team             | Many-to-Many            | Project                           | Multiple teams collaborate on projects   | `project_team`              |
| User             | One-to-Many             | Task (assigned tasks)             | Users are responsible for tasks          | `assignee_id`               |
| User             | One-to-Many             | Task (created tasks)              | User created operational task            | `creator_id`                |
| Project          | One-to-Many             | Task                              | Projects contain tasks                   | direct FK                   |
| Task             | Polymorphic One-to-Many | Comment                           | Tasks can receive comments               | `commentable_type/id`       |
| Project          | Polymorphic One-to-Many | Comment                           | Projects can receive comments            | `commentable_type/id`       |
| Task             | Polymorphic One-to-Many | Attachment                        | Tasks can contain files                  | `attachable_type/id`        |
| Project          | Polymorphic One-to-Many | Attachment                        | Projects can contain files               | `attachable_type/id`        |
| User             | One-to-Many             | Comment                           | User authors comments                    | direct FK                   |
| User             | One-to-Many             | Notification                      | Notifications belong to users            | direct FK                   |
| User             | One-to-Many             | ActivityLog (actor)               | User performs system actions             | `actor_id`                  |
| Task             | Polymorphic One-to-Many | ActivityLog                       | Tasks generate activities                | `subject_type/id`           |
| Project          | Polymorphic One-to-Many | ActivityLog                       | Projects generate activities             | `subject_type/id`           |
| Organization     | One-to-Many             | Subscription                      | Organization owns billing subscription   | direct FK                   |
| SubscriptionPlan | One-to-Many             | Subscription                      | Plans define organization capabilities   | direct FK                   |


Users ↔ Teams:team_user
team_id
user_id
role
joined_at

Projects ↔ Teams:project_team
project_id
team_id
assigned_at