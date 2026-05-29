<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

test('project creation shows a validation error when the slug already exists', function () {
    $user = User::factory()->create();
    $organization = Organization::create([
        'owner_id' => $user->id,
        'name' => 'Acme',
        'slug' => 'acme',
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);
    $workspace = Workspace::create([
        'organization_id' => $organization->id,
        'name' => 'General Workspace',
        'description' => 'Default workspace',
    ]);

    Project::create([
        'workspace_id' => $workspace->id,
        'owner_id' => $user->id,
        'name' => 'Launch Plan',
        'slug' => 'launch-plan',
        'status' => 'active',
    ]);

    $this->actingAs($user);

    Livewire::test('projects.create-project', ['workspace' => $workspace])
        ->set('name', 'Launch Plan')
        ->call('createProject')
        ->assertHasErrors(['name'])
        ->assertSee('A project with that name already exists.');

    expect(Project::where('slug', 'launch-plan')->count())->toBe(1);
});

test('organization creation shows a validation error when the slug already exists', function () {
    $user = User::factory()->create();

    Organization::create([
        'owner_id' => $user->id,
        'name' => 'Acme',
        'slug' => 'acme',
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ]);

    $this->actingAs($user);

    Livewire::test('organizations.create-organization')
        ->set('name', 'Acme')
        ->call('createOrganization')
        ->assertHasErrors(['name'])
        ->assertSee('An organization with that name already exists.');

    expect(Organization::where('slug', 'acme')->count())->toBe(1);
});
