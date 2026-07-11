<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_personal' => false,
            'workspace_id' => fn () => $this->workspaceId(),
        ];
    }

    private function workspaceId(): int
    {
        $owner = User::query()->create([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $organizationName = fake()->unique()->company();

        $organization = Organization::query()->create([
            'name' => $organizationName,
            'slug' => Str::slug($organizationName),
            'owner_id' => $owner->id,
        ]);

        $workspaceName = "{$organizationName} Workspace";

        return Workspace::query()->create([
            'name' => $workspaceName,
            'slug' => Str::slug($workspaceName),
            'organization_id' => $organization->id,
            'description' => 'Default workspace',
            'is_default' => true,
        ])->id;
    }

    /**
     * Indicate that the team is a personal team.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_personal' => true,
        ]);
    }

    /**
     * Indicate that the team has been deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
