<?php

namespace Database\Seeders;

use App\Domain\Billing\Enum\PaymentProvider;
use App\Domain\Billing\Enum\PaymentStatus;
use App\Domain\Billing\SubscriptionStatus;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskStatus;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Organization;
use App\Models\PaymentTransaction;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\CarbonInterface;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SubscriptionPlanSeeder::class);

        $freePlan = SubscriptionPlan::where('slug', 'free')->firstOrFail();
        $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->firstOrFail();
        $teamPlan = SubscriptionPlan::where('slug', 'team-yearly')->firstOrFail();

        $plans = [
            'free' => $freePlan,
            'pro' => $proPlan,
            'team' => $teamPlan,
        ];

        // Platform owner / admin
        $owner = User::firstOrCreate(
            ['email' => 'mac.systems.dev@gmail.com'],
            [
                'name' => 'Mac Systems',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ],
        );

        // Demo members
        $alice = $this->createUser('alice@taskforge.com', 'Alice Johnson');
        $bob = $this->createUser('bob@taskforge.com', 'Bob Brown');
        $carol = $this->createUser('carol@taskforge.com', 'Carol Davis');

        /*
         * Each org config:
         * - plan key from $plans
         * - subscription status
         * - whether the subscription is a trial
         */
        $organizations = [
            [
                'name' => 'Acme Corporation',
                'slug' => 'acme-corporation',
                'plan' => 'pro',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Globex Industries',
                'slug' => 'globex-industries',
                'plan' => 'pro',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Initech',
                'slug' => 'initech',
                'plan' => 'pro',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Umbrella Labs',
                'slug' => 'umbrella-labs',
                'plan' => 'team',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Stark & Sons',
                'slug' => 'stark-and-sons',
                'plan' => 'team',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Wayne Enterprises',
                'slug' => 'wayne-enterprises',
                'plan' => 'team',
                'status' => SubscriptionStatus::PAST_DUE,
                'trial' => false,
            ],
            [
                'name' => 'Hooli',
                'slug' => 'hooli',
                'plan' => 'pro',
                'status' => SubscriptionStatus::TRIAL,
                'trial' => true,
            ],
            [
                'name' => 'Pied Piper',
                'slug' => 'pied-piper',
                'plan' => 'free',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Dunder Mifflin',
                'slug' => 'dunder-mifflin',
                'plan' => 'free',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
            [
                'name' => 'Vandelay Industries',
                'slug' => 'vandelay-industries',
                'plan' => 'free',
                'status' => SubscriptionStatus::ACTIVE,
                'trial' => false,
            ],
        ];

        $createdOrganizations = [];

        foreach ($organizations as $index => $config) {
            $plan = $plans[$config['plan']];

            $organization = Organization::create([
                'name' => $config['name'],
                'slug' => $config['slug'],
                'owner_id' => $owner->id,
            ]);

            // Attach platform owner as organization owner
            $organization->members()->attach($owner->id, [
                'role' => OrganizationRole::OWNER->value,
                'status' => 'active',
                'joined_at' => now()->subMonths(rand(1, 6)),
            ]);

            // Attach a couple of demo members
            $organization->members()->attach($alice->id, [
                'role' => OrganizationRole::MEMBER->value,
                'status' => 'active',
                'joined_at' => now()->subMonths(rand(1, 3)),
            ]);

            if ($index % 2 === 0) {
                $organization->members()->attach($bob->id, [
                    'role' => OrganizationRole::MEMBER->value,
                    'status' => 'active',
                    'joined_at' => now()->subMonths(rand(1, 3)),
                ]);
            }

            /*
             * Create the subscription.
             */
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $plan->id,
                'status' => $config['status'],
                'starts_at' => now()->subMonths(rand(1, 6)),
                'ends_at' => $this->subscriptionEndDate($plan, $config['status']),
                'trial_starts_at' => $config['trial'] ? now()->subDays(rand(1, 7)) : null,
                'trial_ends_at' => $config['trial'] ? now()->addDays(rand(3, 14)) : null,
                'has_used_trial' => ! $config['trial'],
            ]);

            /*
             * Successful payment transactions for paid, non-trial organizations.
             */
            if (! $config['trial'] && $config['plan'] !== 'free') {
                for ($j = 1; $j <= rand(1, 5); $j++) {
                    PaymentTransaction::create([
                        'idempotency_key' => (string) Str::uuid(),
                        'organization_id' => $organization->id,
                        'subscription_plan_id' => $plan->id,
                        'provider' => PaymentProvider::STRIPE,
                        'amount' => $plan->price,
                        'currency' => $plan->currency,
                        'status' => PaymentStatus::SUCCESSFUL,
                        'paid_at' => now()->subMonths(rand(0, 6))->subDays(rand(0, 30)),
                        'provider_reference' => 'ch_' . bin2hex(random_bytes(8)),
                    ]);
                }
            }

            /*
             * Workspaces, teams, projects, and tasks.
             */
            $workspaceCount = match ($config['plan']) {
                'pro' => rand(3, 5),
                'team' => rand(2, 4),
                default => rand(1, 2),
            };

            for ($w = 1; $w <= $workspaceCount; $w++) {
                $workspace = Workspace::create([
                    'organization_id' => $organization->id,
                    'name' => "{$config['name']} Workspace {$w}",
                    'slug' => "{$config['slug']}-workspace-{$w}",
                    'description' => "Workspace {$w} for {$config['name']}",
                    'is_default' => $w === 1,
                ]);

                $teamCount = match ($config['plan']) {
                    'pro', 'team' => rand(2, 4),
                    default => rand(1, 2),
                };

                for ($t = 1; $t <= $teamCount; $t++) {
                    $team = Team::create([
                        'workspace_id' => $workspace->id,
                        'name' => "{$config['name']} Team {$t}",
                        'slug' => "{$config['slug']}-team-{$w}-{$t}",
                        'description' => "Team {$t} for {$config['name']}",
                    ]);

                    // Team leader and member
                    $team->members()->attach($owner->id, [
                        'role' => TeamRole::LEADER->value,
                    ]);

                    $team->members()->attach($alice->id, [
                        'role' => TeamRole::MEMBER->value,
                    ]);

                    if ($index % 2 === 0) {
                        $team->members()->attach($bob->id, [
                            'role' => TeamRole::MEMBER->value,
                        ]);
                    }

                    $projectCount = match ($config['plan']) {
                        'pro' => rand(3, 5),
                        'team' => rand(2, 4),
                        default => rand(1, 2),
                    };

                    for ($p = 1; $p <= $projectCount; $p++) {
                        $project = Project::create([
                            'workspace_id' => $workspace->id,
                            'team_id' => $team->id,
                            'name' => "{$config['name']} Project {$p}",
                            'slug' => "{$config['slug']}-project-{$w}-{$t}-{$p}",
                            'description' => "Project {$p} for {$team->name}",
                            'status' => $this->randomProjectStatus(),
                            'due_date' => now()->addDays(rand(5, 90)),
                            'created_by' => $owner->id,
                        ]);

                        $taskCount = rand(3, 12);

                        for ($k = 1; $k <= $taskCount; $k++) {
                            Task::create([
                                'project_id' => $project->id,
                                'slug' => 'task-' . Str::slug("{$config['slug']}-p{$p}-task-{$k}") . '-' . uniqid(),
                                'title' => "Task {$k} for {$project->name}",
                                'description' => "Task {$k} description for {$project->name}",
                                'status' => $this->randomTaskStatus(),
                                'priority' => $this->randomTaskPriority(),
                                'due_date' => now()->addDays(rand(1, 30)),
                                'assignee_id' => $owner->id,
                                'creator_id' => $owner->id,
                            ]);
                        }
                    }
                }
            }

            /*
             * Refresh usage counts and set realistic storage usage.
             */
            $usage = $organization->usage()->firstOrCreate();

            $usage->update([
                'workspaces_count' => $organization->workspaces()->count(),
                'projects_count' => $organization->projects()->count(),
                'teams_count' => $organization->teams()->count(),
                'members_count' => $organization->members()->count(),
                'tasks_count' => $organization->projects()->withCount('tasks')->get()->sum('tasks_count'),
                'storage_used_bytes' => rand(2, 80) * 1024 * 1024, // 2 MB to 80 MB
                'stored_files_count' => rand(1, 150),
                'voice_notes_count' => rand(0, 30),
            ]);

            $createdOrganizations[] = $organization;
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info('📊 Organizations: ' . Organization::count());
        $this->command->info('👥 Users: ' . User::count());
        $this->command->info('📁 Workspaces: ' . Workspace::count());
        $this->command->info('🏢 Teams: ' . Team::count());
        $this->command->info('📋 Projects: ' . Project::count());
        $this->command->info('✅ Tasks: ' . Task::count());
        $this->command->info('💳 Transactions: ' . PaymentTransaction::count());
    }

    private function createUser(string $email, string $name): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ],
        );
    }

    private function subscriptionEndDate(SubscriptionPlan $plan, SubscriptionStatus $status): ?CarbonInterface
    {
        if ($status === SubscriptionStatus::ACTIVE) {
            return now()->addMonths($plan->billing_interval === \App\Domain\Billing\BillingInterval::YEARLY ? 12 : 1);
        }

        if ($status === SubscriptionStatus::TRIAL) {
            return null;
        }

        return now()->subDays(rand(1, 15));
    }

    private function randomProjectStatus(): ProjectStatus
    {
        return collect([
            ProjectStatus::Active,
            ProjectStatus::Active,
            ProjectStatus::Active,
            ProjectStatus::Completed,
            ProjectStatus::Cancelled,
        ])->random();
    }

    private function randomTaskStatus(): TaskStatus
    {
        return collect([
            TaskStatus::TODO,
            TaskStatus::TODO,
            TaskStatus::IN_PROGRESS,
            TaskStatus::IN_PROGRESS,
            TaskStatus::BLOCKED,
            TaskStatus::DONE,
            TaskStatus::DONE,
            TaskStatus::CANCELLED,
        ])->random();
    }

    private function randomTaskPriority(): TaskPriority
    {
        return collect([
            TaskPriority::LOW,
            TaskPriority::MEDIUM,
            TaskPriority::MEDIUM,
            TaskPriority::HIGH,
            TaskPriority::URGENT,
        ])->random();
    }
}