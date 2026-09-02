<?php

use App\Models\FileAttachment;
use App\Models\Project;
use App\Models\StoredFile;
use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Support\Facades\Storage;

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

test('html file is served as attachment not inline', function () {
    $this->actingAs($this->user);

    // Create HTML file with XSS payload
    $htmlContent = '<script>alert("XSS")</script>';
    Storage::disk('private')->put('test.html', $htmlContent);

    $storedFile = StoredFile::create([
        'organization_id' => $this->org->id,
        'uploaded_by' => $this->user->id,
        'disk' => 'private',
        'path' => 'test.html',
        'stored_name' => 'test.html',
        'original_name' => 'test.html',
        'mime_type' => 'text/html',
        'extension' => 'html',
        'category' => 'document',
        'visibility' => 'project',
        'size' => strlen($htmlContent),
        'checksum' => 'test_html_checksum',
    ]);

    $attachment = FileAttachment::create([
        'stored_file_id' => $storedFile->id,
        'attachable_type' => Project::class,
        'attachable_id' => $this->project->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('projects.attachments.view', [
        'project' => $this->project,
        'attachment' => $attachment,
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Disposition');
    $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
});

test('svg file is served as attachment not inline', function () {
    $this->actingAs($this->user);

    // Create SVG with XSS payload
    $svgContent = '<svg onload="alert(1)"></svg>';
    Storage::disk('private')->put('test.svg', $svgContent);

    $storedFile = StoredFile::create([
        'organization_id' => $this->org->id,
        'uploaded_by' => $this->user->id,
        'disk' => 'private',
        'path' => 'test.svg',
        'stored_name' => 'test.svg',
        'original_name' => 'test.svg',
        'mime_type' => 'image/svg+xml',
        'extension' => 'svg',
        'category' => 'image',
        'visibility' => 'project',
        'size' => strlen($svgContent),
        'checksum' => 'test_svg_checksum',
    ]);

    $attachment = FileAttachment::create([
        'stored_file_id' => $storedFile->id,
        'attachable_type' => Project::class,
        'attachable_id' => $this->project->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('projects.attachments.view', [
        'project' => $this->project,
        'attachment' => $attachment,
    ]));

    $response->assertOk();
    $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
});

test('pdf file can be viewed inline', function () {
    $this->actingAs($this->user);

    // Create minimal PDF
    $pdfContent = '%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF';
    Storage::disk('private')->put('test.pdf', $pdfContent);

    $storedFile = StoredFile::create([
        'organization_id' => $this->org->id,
        'uploaded_by' => $this->user->id,
        'disk' => 'private',
        'path' => 'test.pdf',
        'stored_name' => 'test.pdf',
        'original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'extension' => 'pdf',
        'category' => 'document',
        'visibility' => 'project',
        'size' => strlen($pdfContent),
        'checksum' => 'test_pdf_checksum',
    ]);

    $attachment = FileAttachment::create([
        'stored_file_id' => $storedFile->id,
        'attachable_type' => Project::class,
        'attachable_id' => $this->project->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('projects.attachments.view', [
        'project' => $this->project,
        'attachment' => $attachment,
    ]));

    $response->assertOk();
    $this->assertStringNotContainsString('attachment', $response->headers->get('Content-Disposition', ''));
});
