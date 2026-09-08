# Authorization & Locking Fixes

## 1. Project Index Page

- **Issue:** Users saw projects they could not view, leading to 403 after click.
- **Fix:** Implemented SQL-based policy filtering in `resources/views/livewire/projects/index-projects.blade.php`.
  - Added `viewableBy` equivalent logic directly in the query:
    - Organization owner/admin or team member can view non-locked projects.
    - Locked projects are excluded for all users.
  - Locked projects determined by:
    - Organization plan `max_projects` limit (`lockedProjects()`).
    - Locked workspaces (`lockedWorkspaces()`).
    - Locked teams (`lockedTeams()`).
  - Used `whereNotIn` to exclude locked project IDs, maintaining native pagination.

## 2. Task Index Page

- **Issue:** Similar unauthorized visibility and incorrect `due_soon` filter.
- **Fix:** Updated `resources/views/livewire/tasks/index-tasks.blade.php`.
  - Excluded tasks belonging to locked projects using `whereNotIn(tasks.project_id, $lockedProjectIds)`.
  - Replaced the single team membership condition with a three-way `orWhere`:
    - Organization owner
    - Organization admin (active membership)
    - Team member
  - Fixed `due_soon` filter:
    - Now uses date range (`whereBetween(due_date, [now(), now()->addDays(7)])`) instead of matching status.
    - Properly excludes `DONE` and `CANCELLED` tasks.
  - Dashboard "View all" link for Due Soon passes `statusFilter=due_soon`, and the button in the index page works correctly.

## 3. Workspace Index Page

- **Issue:** Locked workspaces were either hidden or caused 403 when accessed.
- **Fix:** Updated `resources/views/livewire/workspaces/index-workspaces.blade.php`.
  - Removed the previous `whereNotIn` exclusion.
  - Now lists all accessible workspaces, including locked ones.
  - Added a lock badge and disabled navigation for locked workspaces:
    - Grayed out card with `opacity-60 pointer-events-none`.
    - Shows `🔒 Locked` badge.
    - `href` becomes `#` and `aria-disabled="true"` to prevent accidental access.

## 4. Policy Updates

- Updated `app/Policies/TaskPolicy.php` `view()` method:
  - Allows organization owner/admin even for locked tasks? No, locked tasks are still blocked by `$this->locked($task)` check.
  - For non-locked tasks, now checks organization owner/admin before falling back to team role.

## 5. Demo Data Verification

- Confirmed `SubscriptionPlanSeeder` plan limits:
  - Free: max_workspaces=1, max_projects=5, max_tasks=25
  - Pro: max_workspaces=5, max_projects=20, max_tasks=500
  - Team: max_workspaces=10, max_projects=50, max_tasks=2000
- `DemoDataSeeder` generates organizations with varying project counts, causing locked projects/workspaces.
- Verified that `Pied Piper` has a locked workspace (ID 30) due to exceeding the free plan limit.

## 6. Testing Performed

- Ran tinker queries to confirm locked project IDs are excluded from index queries.
- Tested workspace index: locked workspace appears with lock badge and is not clickable.
- Confirmed due soon filter works from dashboard link and button.

# Future Plans

- **Locked Route Exception Handling:**
  - Currently, if a user manually navigates to a locked workspace/project/task, they receive a 403.
  - Goal: throw a specific `LockedResourceException` or similar, which can be rendered with a friendly message explaining the plan limit.
  - Implement middleware or policy updates to throw exception instead of returning false for locked resources.

- **Centralized Viewable Scopes:**
  - Move the SQL-based policy filtering into reusable model scopes (`scopeViewableBy`) to avoid duplication across components.

- **Testing Automation:**
  - Add feature tests for project/task/workspace index visibility based on roles and lock status.


## 7. Pulse JSON Exception (Recurring)

- **Symptom:** Pulse dashboard throws `JsonException` in the Exceptions card.
- **Root Cause:** Stale malformed JSON in `pulse_entries` or `pulse_aggregates`, or Pulse data serialized with an incompatible Redis client/serializer.
- **Permanent Fix:**
  - Use **phpredis** client, not Predis.
  - Set Redis serializer to PHP:
    ```php
    // config/database.php -> redis.options
    "serializer" => 1, // Redis::SERIALIZER_PHP
In .env:

dotenv
REDIS_CLIENT=phpredis
Cleanup Commands:

bash
docker compose up -d --force-recreate app
docker compose exec app php artisan tinker --execute="
Illuminate\\Support\\Facades\\Redis::connection(\"cache\")->flushdb();
DB::table(\"pulse_values\")->truncate();
DB::table(\"pulse_entries\")->truncate();
DB::table(\"pulse_aggregates\")->truncate();
echo \"Pulse data cleared\";
"
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
If JsonException returns: Check for invalid JSON in pulse_entries.key:

bash
docker compose exec app php artisan tinker --execute="
foreach (DB::table(\"pulse_entries\")->where(\"type\", \"exception\")->get() as \\$row) {
    json_decode(\\$row->key);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo \\$row->id . PHP_EOL . \\$row->key . PHP_EOL;
    }
}
"
Delete any invalid rows or truncate again.

8. Redis Client & Serializer Reference
Item	Value
Client	phpredis
Serializer	1 (Redis::SERIALIZER_PHP)
Cache DB	REDIS_CACHE_DB=1
Pulse Cache Driver	Redis via default cache
9. Testing & Next Steps
□ Verify /projects, /tasks, /workspaces index pages show only authorized items.
□ Confirm locked workspaces/projects appear with lock badge and are not clickable.
□ Confirm Pulse dashboard loads without JsonException.
□ Add feature tests for index visibility + locked resources.
□ Consider centralizing viewable scopes in models.
