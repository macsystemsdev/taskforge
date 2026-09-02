<?php

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\Teams\TeamMemberAdded;
use App\Notifications\Teams\TeamMemberAddedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('team member role can be updated by leader', function () {
    $leader = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($leader, ['role' => TeamRole::LEADER->value]);
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);

    $this->actingAs($leader);

    Livewire::test('pages::teams.edit', ['team' => $team])
        ->call('updateMember', $member->id, TeamRole::LEADER->value)
        ->assertHasNoErrors();

    expect($team->members()->where('user_id', $member->id)->first()->pivot->role)->toEqual(TeamRole::LEADER->value);
});

test('adding an organization member to a team sends a database notification', function () {
    Notification::fake();

    $leader = User::factory()->create();
    $admin = User::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $organization = Organization::create([
        'name' => 'Test Organization',
        'slug' => 'test-organization',
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
        'owner_id' => $owner->id,
    ]);

    $workspace = Workspace::create([
        'organization_id' => $organization->id,
        'name' => 'General Workspace',
        'slug' => 'team-member-general-workspace',
        'description' => 'Default workspace',
        'is_default' => true,
    ]);

    $team = Team::factory()->create(['workspace_id' => $workspace->id]);

    $organization->members()->attach($admin, [
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $organization->members()->attach($member, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $team->members()->attach($leader, ['role' => TeamRole::LEADER->value]);

    $this->actingAs($leader);

    Livewire::test('pages::teams.add-member-modal', ['team' => $team])
        ->call('addMember', $member->id)
        ->assertHasNoErrors();

    expect($member->fresh()->belongsToTeam($team))->toBeTrue();

    Notification::assertSentTo(
        $member,
        TeamMemberAddedNotification::class,
        fn ($notification, $channels) => in_array('database', $channels, true)
    );
});

test('team member role cannot be updated by non owner', function () {
    $leader = User::factory()->create();
    $nonOwner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($leader, ['role' => TeamRole::LEADER->value]);
    $team->members()->attach($nonOwner, ['role' => TeamRole::MEMBER->value]);
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);

    $this->actingAs($nonOwner);

    Livewire::test('pages::teams.edit', ['team' => $team])
        ->call('updateMember', $member->id, TeamRole::LEADER->value)
        ->assertForbidden();
});

test('team member can be removed by owner', function () {
    $leader = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($leader, ['role' => TeamRole::LEADER->value]);
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);

    $this->actingAs($leader);

    Livewire::test('pages::teams.remove-member-modal', ['team' => $team])
        ->set('memberId', $member->id)
        ->call('removeMember')
        ->assertHasNoErrors();

    expect($team->members()->where('user_id', $member->id)->exists())->toBeFalse();
});

test('team member cannot be removed by non owners', function () {

    $leader = User::factory()->create();
    $nonOwner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

   
    $team->members()->attach($leader, ['role' => TeamRole::LEADER->value]);
    $team->members()->attach($nonOwner, ['role' => TeamRole::MEMBER->value]);
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);

    $this->actingAs($nonOwner);

    Livewire::test('pages::teams.remove-member-modal', ['team' => $team])
        ->set('memberId', $member->id)
        ->call('removeMember')
        ->assertForbidden();
});


