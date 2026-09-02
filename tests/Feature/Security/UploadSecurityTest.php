<?php

use App\Livewire\Comments\CommentSection;
use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\StoredFile;
use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('private');

    // Create Organization A with owner
    [$this->orgA, $this->userA] = $this->createOrganizationWithOwner();

    // Create Organization B with owner
    [$this->orgB, $this->userB] = $this->createOrganizationWithOwner();

    // Create workspaces
    $this->workspaceA = Workspace::create([
        'organization_id' => $this->orgA->id,
        'name' => 'Workspace A',
        'slug' => 'workspace-a-' . uniqid(),
        'description' => 'Test workspace A',
        'is_default' => true,
    ]);

    $this->workspaceB = Workspace::create([
        'organization_id' => $this->orgB->id,
        'name' => 'Workspace B',
        'slug' => 'workspace-b-' . uniqid(),
        'description' => 'Test workspace B',
        'is_default' => true,
    ]);

    // Create teams
    $teamA = Team::create([
        'workspace_id' => $this->workspaceA->id,
        'name' => 'Team A',
        'slug' => 'team-a-' . uniqid(),
        'is_personal' => false,
        'created_by' => $this->userA->id,
    ]);

    $teamB = Team::create([
        'workspace_id' => $this->workspaceB->id,
        'name' => 'Team B',
        'slug' => 'team-b-' . uniqid(),
        'is_personal' => false,
        'created_by' => $this->userB->id,
    ]);

    // Create projects
    $this->projectA = Project::create([
        'workspace_id' => $this->workspaceA->id,
        'team_id' => $teamA->id,
        'created_by' => $this->userA->id,
        'name' => 'Project A',
        'slug' => 'project-a-' . uniqid(),
        'status' => 'active',
    ]);

    $this->projectB = Project::create([
        'workspace_id' => $this->workspaceB->id,
        'team_id' => $teamB->id,
        'created_by' => $this->userB->id,
        'name' => 'Project B',
        'slug' => 'project-b-' . uniqid(),
        'status' => 'active',
    ]);
});

test('executable php file is rejected', function () {
    $this->actingAs($this->userA);

    $file = UploadedFile::fake()->createWithContent(
        'malicious.php',
        '<?php echo "owned"; ?>'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->projectA])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('executable file with spoofed extension is rejected', function () {
    $this->actingAs($this->userA);

    $file = UploadedFile::fake()->createWithContent(
        'not-really-image.jpg',
        '<?php echo "owned"; ?>'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->projectA])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('double extension is rejected', function () {
    $this->actingAs($this->userA);

    $file = UploadedFile::fake()->createWithContent(
        'shell.php.jpg',
        '<?php echo "owned"; ?>'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->projectA])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);
});

test('user from other tenant cannot download attachment', function () {
    $this->actingAs($this->userA);

    // Create a legitimate attachment in Org A
    Storage::disk('private')->put('test.pdf', 'test content');

    $storedFile = StoredFile::create([
        'organization_id' => $this->orgA->id,
        'uploaded_by' => $this->userA->id,
        'disk' => 'private',
        'path' => 'test.pdf',
        'stored_name' => 'test.pdf',
        'original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'category' => 'document',
        'visibility' => 'project',
        'size' => 100,
        'checksum' => 'test_checksum',
    ]);

    $attachment = FileAttachment::create([
        'stored_file_id' => $storedFile->id,
        'attachable_type' => Project::class,
        'attachable_id' => $this->projectA->id,
        'created_by' => $this->userA->id,
    ]);

    // User B tries to download User A's attachment
    $this->actingAs($this->userB);

    $response = $this->get(route('projects.attachments.download', [
        'project' => $this->projectA,
        'attachment' => $attachment,
    ]));

    $response->assertForbidden();
});

test('legitimate file upload succeeds on private disk', function () {
    $this->actingAs($this->userA);

    $file = UploadedFile::fake()->createWithContent(
        'test.pdf',
        '%PDF-1.4
1 0 obj
<< /Type /Catalog >>
endobj
trailer
<< /Root 1 0 R >>
%%EOF'
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->projectA])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasNoErrors(['file', 'uploads.*']);

    // Verify file stored on private disk
    $storedFile = StoredFile::latest()->first();
    expect($storedFile)->not->toBeNull()
        ->and($storedFile->disk)->toBe('private');

    Storage::disk('private')->assertExists($storedFile->path);
});
