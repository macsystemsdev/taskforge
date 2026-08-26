<?php

use App\Models\Organization;
use App\Models\User;
use Livewire\Livewire;

test('project creation shows a validation error when name is empty', function () {
    $user = User::factory()->create();
    $workspace = $user->currentTeam->workspace;
    $team = $user->currentTeam;
    
    $this->actingAs($user);
    
    Livewire::test('projects.create-project', ['workspace' => $workspace])
        ->set('name', '')
        ->set('teamId', $team->id)
        ->call('createProject')
        ->assertHasErrors(['name' => 'required']);
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
