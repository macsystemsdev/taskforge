<?php

use App\Livewire\Comments\CommentSection;
use App\Models\FileAttachment;
use App\Models\Organization;
use App\Models\Project;
use App\Models\StoredFile;
use App\Models\SubscriptionPlan;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('private');

    // Create plans first
    $this->createBillingPlans();

    // Create plan with small storage limit
    $this->plan = SubscriptionPlan::create([
        'name' => 'Limited Plan',
        'slug' => 'limited',
        'price' => 0,
        'currency' => 'USD',
        'billing_interval' => 'none',
        'status' => 'active',
        'max_workspaces' => 1,
        'max_projects' => 5,
        'max_members' => 5,
        'max_storage_mb' => 1, // 1 MB limit
        'max_tasks' => 10,
        'max_teams' => 5,
    ]);

    // Create organization with this plan
    [$this->org, $this->user] = $this->createOrganizationWithOwner();

    // Ensure usage record exists
    $this->org->usage()->firstOrCreate([
        'storage_used_bytes' => 0,
    ]);

    // Update subscription to use limited plan
    $subscription = $this->org->subscription;
    $subscription->update([
        'subscription_plan_id' => $this->plan->id,
    ]);



    // Create workspace and project
    $this->workspace = Workspace::create([
        'organization_id' => $this->org->id,
        'name' => 'Test Workspace',
        'slug' => 'test-workspace-' . uniqid(),
        'description' => 'Test',
        'is_default' => true,
    ]);

    $team = Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Test Team',
        'slug' => 'test-team-' . uniqid(),
        'is_personal' => false,
        'created_by' => $this->user->id,
    ]);

    $this->project = Project::create([
        'workspace_id' => $this->workspace->id,
        'team_id' => $team->id,
        'created_by' => $this->user->id,
        'name' => 'Test Project',
        'slug' => 'test-project-' . uniqid(),
        'status' => 'active',
    ]);
});

test('upload over quota is rejected', function () {
    $this->actingAs($this->user);

    // Create a file larger than 1 MB
    $file = UploadedFile::fake()->createWithContent(
        'large.pdf',
        str_repeat('A', 2 * 1024 * 1024) // 2 MB
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('duplicate upload still counts against quota', function () {
    $this->actingAs($this->user);

    // Create a 700 KB PDF file (under 1 MB limit)
    $content = '%PDF-1.4
' . str_repeat('B', 700 * 1024);
    $file = UploadedFile::fake()->createWithContent('test.pdf', $content);

    // First upload succeeds
    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasNoErrors();

    // Second upload of same file should fail because 700KB + 700KB > 1MB
    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('user without organization can still upload avatar', function () {
    // Create user without organization
    $userWithoutOrg = User::factory()->create();
    $this->actingAs($userWithoutOrg);

    // Create minimal valid JPEG
    $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==');
    $avatar = UploadedFile::fake()->createWithContent(
        'avatar.jpg',
        $jpegContent . str_repeat('C', 100 * 1024) // 100 KB avatar
    );

    Livewire::test('pages::settings.profile')
        ->set('avatar', $avatar)
        ->call('updateAvatar')
        ->assertHasNoErrors();

    // Verify avatar was stored
    expect($userWithoutOrg->fresh()->avatar_path)->not->toBeNull();
});
