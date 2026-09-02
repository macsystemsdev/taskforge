<?php

namespace Database\Factories;

use App\Domain\Billing\BillingInterval;
use App\Domain\Billing\SubscriptionPlanStatus;
use App\Domain\Organizations\Enums\OrganizationRole;
use App\Domain\Teams\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Create free plan if not exists
            $freePlan = SubscriptionPlan::firstOrCreate(
                ['slug' => 'free'],
                [
                    'name' => 'Free',
                    'price' => 0,
                    'currency' => 'USD',
                    'billing_interval' => BillingInterval::NONE,
                    'status' => SubscriptionPlanStatus::ACTIVE,
                    'max_workspaces' => 1,
                    'max_projects' => 5,
                    'max_members' => 5,
                ]
            );

            // Create organization
            $organization = Organization::create([
                'owner_id' => $user->id,
                'name' => "{$user->name}'s Org",
                'slug' => Str::slug($user->name . '-' . $user->id),
                'subscription_plan' => 'free',
                'subscription_status' => 'active',
            ]);

            // Attach owner as member
            $organization->members()->attach($user->id, [
                'role' => OrganizationRole::OWNER->value,
            ]);

            // Create subscription
            Subscription::create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);

            // Create default workspace
            $workspace = Workspace::create([
                'organization_id' => $organization->id,
                'name' => 'Default Workspace',
                'slug' => Str::slug('default-workspace-' . $user->id),
                'description' => 'Default workspace',
                'is_default' => true,
            ]);

            // Create personal team
            $team = Team::create([
                'workspace_id' => $workspace->id,
                'name' => "{$user->name}'s Team",
                'slug' => Str::slug($user->name . '-team-' . $user->id),
                'is_personal' => true,
            ]);

            // Attach user as team leader
            $team->members()->attach($user->id, [
                'role' => TeamRole::LEADER->value,
            ]);

            // Set current team
            $user->update(['current_team_id' => $team->id]);
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
