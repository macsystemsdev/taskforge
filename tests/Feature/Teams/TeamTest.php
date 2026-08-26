<?php

use App\Domain\Teams\Enums\TeamRole;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

test('teams index page can be rendered', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertOk();
});

test('teams can be created', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.create-team')
        ->set('name', 'Test Team')
        ->call('createTeam')
        ->assertHasNoErrors();
    
    $this->assertDatabaseHas('teams', ['name' => 'Test Team']);
});

test('team slug uses next available suffix', function () {
    $user = User::factory()->create();
    $workspace = $user->currentTeam->workspace;
    
    Team::factory()->create(['name' => 'Test', 'slug' => 'test', 'workspace_id' => $workspace->id]);
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.create-team', ['workspace' => $workspace])
        ->set('name', 'Test')
        ->call('createTeam')
        ->assertHasNoErrors();
    
    $this->assertDatabaseHas('teams', ['slug' => 'test-1']);
});

test('creating a team attaches the creator as owner', function () {
    $user = User::factory()->create();
    $workspace = $user->currentTeam->workspace;
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.create-team', ['workspace' => $workspace])
        ->set('name', 'New Team')
        ->call('createTeam')
        ->assertHasNoErrors();
    
    $team = Team::where('name', 'New Team')->first();
    expect($team->members()->where('user_id', $user->id)->first()->pivot->role)->toBe(TeamRole::LEADER->value);
});

test('team edit page can be rendered', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    
    $this->actingAs($user)
        ->get(route('teams.edit', $team))
        ->assertOk();
});

test('teams can be updated by owners', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.edit-team', ['team' => $team])
        ->set('name', 'Updated Team')
        ->call('updateTeam')
        ->assertHasNoErrors();
    
    expect($team->fresh()->name)->toBe('Updated Team');
});

test('teams cannot be updated by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = $owner->currentTeam;
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);
    
    $this->actingAs($member);
    
    Livewire::test('pages::teams.edit-team', ['team' => $team])
        ->set('name', 'Hacked Team')
        ->call('updateTeam')
        ->assertForbidden();
});

test('teams can be deleted by owners', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
        ->set('deleteName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();
    
    $this->assertSoftDeleted('teams', ['id' => $team->id]);
});

test('team deletion requires name confirmation', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
        ->set('deleteName', 'Wrong Name')
        ->call('deleteTeam')
        ->assertHasErrors(['deleteName']);
    
    $this->assertDatabaseHas('teams', ['id' => $team->id]);
});

test('deleting current team switches to alphabetically first remaining team', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $workspace = $personalTeam->workspace;
    
    $alphaTeam = Team::factory()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Alpha Team',
        'slug' => 'alpha-team',
        'is_personal' => false,
    ]);
    
    $zuluTeam = Team::factory()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Zulu Team',
        'slug' => 'zulu-team',
        'is_personal' => false,
    ]);
    
    $user->teams()->attach([$alphaTeam->id, $zuluTeam->id]);
    $user->switchTeam($zuluTeam);
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $zuluTeam])
        ->set('deleteName', $zuluTeam->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();
    
    $this->assertSoftDeleted('teams', ['id' => $zuluTeam->id]);
    
    expect($user->fresh()->current_team_id)->toEqual($alphaTeam->id);
});

test('deleting current team falls back to personal team', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create(['name' => 'Zulu Team', 'is_personal' => false]);
    $team->members()->attach($user, ['role' => TeamRole::LEADER->value]);
    
    $user->update(['current_team_id' => $team->id]);
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
        ->set('deleteName', $team->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();
    
    $this->assertSoftDeleted('teams', ['id' => $team->id]);
    
    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting non current team leaves current team unchanged', function () {
    $user = User::factory()->create();
    $currentTeam = $user->currentTeam;
    $otherTeam = Team::factory()->create(['is_personal' => false]);
    $otherTeam->members()->attach($user, ['role' => TeamRole::LEADER->value]);
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $otherTeam])
        ->set('deleteName', $otherTeam->name)
        ->call('deleteTeam')
        ->assertHasNoErrors();
    
    $this->assertSoftDeleted('teams', ['id' => $otherTeam->id]);
    
    expect($user->fresh()->current_team_id)->toEqual($currentTeam->id);
});

test('personal teams cannot be deleted', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    
    $this->actingAs($user);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $personalTeam])
        ->set('deleteName', $personalTeam->name)
        ->call('deleteTeam')
        ->assertForbidden();
    
    $this->assertDatabaseHas('teams', ['id' => $personalTeam->id]);
});

test('teams cannot be deleted by non owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = $owner->currentTeam;
    $team->members()->attach($member, ['role' => TeamRole::MEMBER->value]);
    
    $this->actingAs($member);
    
    Livewire::test('pages::teams.delete-team-modal', ['team' => $team])
        ->set('deleteName', $team->name)
        ->call('deleteTeam')
        ->assertForbidden();
});

test('guests cannot access teams', function () {
    $response = $this->get(route('teams.index'));
    
    $response->assertRedirect(route('login'));
});
