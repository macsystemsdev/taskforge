<?php

use App\Livewire\Comments\CommentSection;
use App\Models\Project;
use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
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

test('corrupted image file is rejected', function () {
    $this->actingAs($this->user);

    // Create a file that claims to be JPEG but is corrupted
    $file = UploadedFile::fake()->createWithContent(
        'corrupted.jpg',
        'not actually a valid JPEG image'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('valid image passes validation', function () {
    $this->actingAs($this->user);

    // Minimal valid JPEG
    $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==');

    $file = UploadedFile::fake()->createWithContent(
        'valid.jpg',
        $jpegContent
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasNoErrors(['file']);
});

test('polyglot file with image mime but html content is rejected', function () {
    $this->actingAs($this->user);

    // HTML content but with .jpg extension
    $file = UploadedFile::fake()->createWithContent(
        'polyglot.jpg',
        '<html><script>alert("XSS")</script></html>'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});
