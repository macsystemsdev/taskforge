<?php

namespace Database\Factories;

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Organization;
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
            // Create organization
            $organization = Organization::create([
                'owner_id' => $user->id,
                'name' => "{$user->name}'s Org",
                'slug' => Str::slug("{$user->name}-org-" . $user->id),
                'subscription_plan' => 'free',
                'subscription_status' => 'active',
            ]);
            
            // Create workspace
            $workspace = Workspace::create([
                'organization_id' => $organization->id,
                'name' => 'Personal Workspace',
                'slug' => Str::slug("personal-workspace-" . $user->id),
                'description' => 'Personal workspace',
                'is_default' => true,
            ]);
            
            // Create personal team
            $team = Team::create([
                'workspace_id' => $workspace->id,
                'name' => "{$user->name}'s Team",
                'slug' => Str::slug("{$user->name}-team-" . $user->id),
                'is_personal' => true,
            ]);

            $team->members()->attach($user, [
                'role' => TeamRole::LEADER->value,
            ]);

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
