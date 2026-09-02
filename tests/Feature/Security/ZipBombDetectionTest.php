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

test('zip bomb with high compression ratio is rejected', function () {
    $this->actingAs($this->user);

    // Create a zip bomb - highly compressible data
    $zipContent = str_repeat('A', 1000000); // 1MB of 'A's compresses to ~1KB
    
    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'zipbomb');
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addFromString('bomb.txt', $zipContent);
    $zip->close();

    $file = UploadedFile::fake()->createWithContent(
        'bomb.zip',
        file_get_contents($zipFile)
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);

    unlink($zipFile);
});

test('normal zip file passes validation', function () {
    $this->actingAs($this->user);

    // Create a normal zip file
    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'normalzip');
    $zip->open($zipFile, ZipArchive::CREATE);
    $zip->addFromString('document.txt', 'This is a normal text file.');
    $zip->addFromString('data.csv', 'name,value\ntest,123\n');
    $zip->close();

    $file = UploadedFile::fake()->createWithContent(
        'documents.zip',
        file_get_contents($zipFile)
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasNoErrors(['file']);

    unlink($zipFile);
});

test('zip with too many files is rejected', function () {
    $this->actingAs($this->user);

    // Create a zip with more than 1000 files (should be rejected)
    $zip = new ZipArchive();
    $zipFile = tempnam(sys_get_temp_dir(), 'manyfiles');
    $zip->open($zipFile, ZipArchive::CREATE);
    
    for ($i = 0; $i < 1001; $i++) {
        $zip->addFromString("file_{$i}.txt", "content {$i}");
    }
    
    $zip->close();

    $file = UploadedFile::fake()->createWithContent(
        'manyfiles.zip',
        file_get_contents($zipFile)
    );

    Livewire::test(CommentSection::class, ['commentable' => $this->project])
        ->set('uploads', [$file])
        ->call('uploadAttachments')
        ->assertHasErrors(['uploads']);

    unlink($zipFile);
});
