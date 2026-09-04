<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\Team;
use App\Models\Task;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\PaymentTransaction;
use App\Domain\Billing\Enum\PaymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // First, run the SubscriptionPlanSeeder to ensure plans exist
        $this->call(SubscriptionPlanSeeder::class);

        // Get the plans
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        $proPlan = SubscriptionPlan::where('slug', 'pro-monthly')->first();
        $teamPlan = SubscriptionPlan::where('slug', 'team-yearly')->first();

        if (!$freePlan || !$proPlan || !$teamPlan) {
            $this->command->error('❌ Subscription plans not found.');
            return;
        }

        // Create platform owner
        $owner = User::firstOrCreate(
            ['email' => 'mac.systems.dev@gmail.com'],
            [
                'name' => 'Mac Systems',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );

        // Get or create an additional user for team members
        $member = User::firstOrCreate(
            ['email' => 'member@taskforge.com'],
            [
                'name' => 'Demo Member',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );

        $organizations = [];

        // Create 10 organizations
        for ($i = 1; $i <= 10; $i++) {
            $plan = $i <= 3 ? $proPlan : ($i <= 6 ? $teamPlan : $freePlan);
            $status = $i <= 7 ? 'active' : ($i <= 9 ? 'trial' : 'past_due');

            $org = Organization::create([
                'name' => "Organization {$i}",
                'slug' => "org-{$i}",
                'owner_id' => $owner->id,
            ]);

            // Create subscription
            Subscription::create([
                'organization_id' => $org->id,
                'subscription_plan_id' => $plan->id,
                'status' => $status,
                'starts_at' => now()->subMonths(rand(1, 6)),
                'ends_at' => $status === 'active' ? now()->addMonths(rand(1, 12)) : null,
                'trial_ends_at' => $status === 'trialing' ? now()->addDays(rand(1, 14)) : null,
                'has_used_trial' => rand(0, 1),
            ]);

            // Create payment transactions
            for ($j = 1; $j <= rand(1, 5); $j++) {
                PaymentTransaction::create([
                    'organization_id' => $org->id,
                    'subscription_plan_id' => $plan->id,
                    'provider' => 'stripe',
                    'amount' => $plan->price * 100,
                    'currency' => 'USD',
                    'status' => PaymentStatus::SUCCESSFUL,
                    'paid_at' => now()->subMonths(rand(0, 6))->subDays(rand(0, 30)),
                    'provider_reference' => 'ch_' . bin2hex(random_bytes(8)),
                ]);
            }

            $organizations[] = $org;

            // Create workspaces
            $workspaceCount = $i <= 3 ? rand(3, 5) : ($i <= 6 ? rand(2, 3) : rand(1, 2));
            for ($w = 1; $w <= $workspaceCount; $w++) {
                $workspace = Workspace::create([
                    'organization_id' => $org->id,
                    'name' => "Workspace {$w}",
                    'slug' => "workspace-{$i}-{$w}",
                    'description' => "Workspace {$w} for Organization {$i}",
                    'is_default' => $w === 1,
                ]);

                // Create teams
                $teamCount = $i <= 3 ? rand(2, 4) : rand(1, 2);
                for ($t = 1; $t <= $teamCount; $t++) {
                    $team = Team::create([
                        'workspace_id' => $workspace->id,
                        'name' => "Team {$t}",
                        'slug' => "team-{$i}-{$w}-{$t}",
                        'description' => "Team {$t} for Workspace {$w}",
                    ]);

                    // Add owner to team (skip if already attached)
                    if (!$team->members()->where('user_id', $owner->id)->exists()) {
                        $team->members()->attach($owner->id, ['role' => 'owner']);
                    }

                    // Add member to team (skip if already attached)
                    if (!$team->members()->where('user_id', $member->id)->exists()) {
                        $team->members()->attach($member->id, ['role' => 'member']);
                    }

                    // Create projects
                    $projectCount = $i <= 3 ? rand(3, 5) : ($i <= 6 ? rand(2, 3) : rand(1, 2));
                    for ($p = 1; $p <= $projectCount; $p++) {
                        $project = Project::create([
                            'workspace_id' => $workspace->id,
                            'team_id' => $team->id,
                            'name' => "Project {$p}",
                            'slug' => "project-{$i}-{$w}-{$t}-{$p}",
                            'description' => "Project {$p} for Team {$t}",
                            'status' => ['active', 'completed', 'cancelled'][rand(0, 2)],
                            'due_date' => now()->addDays(rand(1, 90)),
                            'created_by' => $owner->id,
                        ]);

                        // Create tasks
                        $taskCount = rand(3, 12);
                        for ($k = 1; $k <= $taskCount; $k++) {
                            $taskStatuses = ['todo', 'in_progress', 'done', 'blocked'];
                            Task::create([
                                'project_id' => $project->id,
                                'slug' => 'task-' . Str::slug("Task {$k} for Project {$p}") . '-' . uniqid(),
                                'title' => "Task {$k} for Project {$p}",
                                'description' => "Task {$k} description",
                                'status' => $taskStatuses[rand(0, 3)],
                                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                                'due_date' => now()->addDays(rand(1, 30)),
                                'assignee_id' => $owner->id,
                                'creator_id' => $owner->id,
                            ]);
                        }
                    }
                }
            }
        }

        // Update usage stats
        foreach ($organizations as $org) {
            $usage = $org->usage()->firstOrCreate();
            $usage->update([
                'workspaces_count' => $org->workspaces()->count(),
                'projects_count' => $org->projects()->count(),
                'teams_count' => $org->teams()->count(),
                'members_count' => $org->members()->count(),
                'tasks_count' => $org->tasks()->count(),
                'storage_used_bytes' => rand(1024 * 1024, 50 * 1024 * 1024),
            ]);
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->info("📊 Organizations: " . Organization::count());
        $this->command->info("👥 Users: " . User::count());
        $this->command->info("📁 Workspaces: " . Workspace::count());
        $this->command->info("🏢 Teams: " . Team::count());
        $this->command->info("📋 Projects: " . Project::count());
        $this->command->info("✅ Tasks: " . Task::count());
        $this->command->info("💳 Transactions: " . PaymentTransaction::count());
    }
}
