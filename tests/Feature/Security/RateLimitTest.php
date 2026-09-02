<?php

use App\Livewire\Comments\CommentSection;
use App\Models\Project;
use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('private');

    // Create org and user
    [$this->org, $this->user] = $this->createOrganizationWithOwner();

    // Create workspace
    $this->workspace = Workspace::create([
        'organization_id' => $this->org->id,
        'name' => 'Test Workspace',
        'slug' => 'test-workspace-' . uniqid(),
        'description' => 'Test',
        'is_default' => true,
    ]);

    // Create team
    $team = Team::create([
        'workspace_id' => $this->workspace->id,
        'name' => 'Test Team',
        'slug' => 'test-team-' . uniqid(),
        'is_personal' => false,
        'created_by' => $this->user->id,
    ]);

    // Create project
    $this->project = Project::create([
        'workspace_id' => $this->workspace->id,
        'team_id' => $team->id,
        'created_by' => $this->user->id,
        'name' => 'Test Project',
        'slug' => 'test-project-' . uniqid(),
        'status' => 'active',
    ]);
});

test('rate limit blocks excessive uploads', function () {
    $this->actingAs($this->user);

    // Create a valid file
    $file = UploadedFile::fake()->createWithContent(
        'test.pdf',
        '%PDF-1.4\n' . str_repeat('A', 1024)
    );

    // Clear any existing rate limiter state
    RateLimiter::clear('upload:' . $this->user->id);

    // Perform 10 uploads (at the limit)
    for ($i = 0; $i < 10; $i++) {
        Livewire::test(CommentSection::class, ['commentable' => $this->project])
            ->set('uploads', [$file])
            ->call('uploadAttachments');
    }

    // 11th upload should be blocked
    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('rate limit does not block normal usage', function () {
    $this->actingAs($this->user);

    $file = UploadedFile::fake()->createWithContent(
        'test.pdf',
        '%PDF-1.4\n' . str_repeat('B', 1024)
    );

    // Clear rate limiter
    RateLimiter::clear('upload:' . $this->user->id);

    // Single upload should succeed
    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasNoErrors(['file']);
});
