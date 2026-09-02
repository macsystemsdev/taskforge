# TaskForge Security & Upload System — Complete Documentation

**Last Updated:** 2026-09-01

---

## Table of Contents

1. Security Architecture Overview
2. File Upload Pipeline
3. Storage Architecture
4. Authorization Model
5. Validation Layers
6. Tenant Isolation
7. Rate Limiting
8. Content-Disposition Security
9. Image Validation
10. Storage Quota Enforcement
11. Security Test Suite
12. Attack Surface Map
13. Known Vulnerabilities (Fixed)
14. Deferred Security Work
15. Testing Commands
16. Files Involved

---

## Security Architecture Overview

TaskForge implements a **defense-in-depth** security model for file uploads. Each layer provides independent protection, so a breach in one layer doesn't compromise the entire system.

# Create the security documentation
cat > docs/SECURITY_AND_UPLOAD_SYSTEM.md << 'EOF'
# TaskForge Security & Upload System — Complete Documentation

**Last Updated:** 2026-09-01

---

## Table of Contents

1. Security Architecture Overview
2. File Upload Pipeline
3. Storage Architecture
4. Authorization Model
5. Validation Layers
6. Tenant Isolation
7. Rate Limiting
8. Content-Disposition Security
9. Image Validation
10. Storage Quota Enforcement
11. Security Test Suite
12. Attack Surface Map
13. Known Vulnerabilities (Fixed)
14. Deferred Security Work
15. Testing Commands
16. Files Involved

---

## Security Architecture Overview

TaskForge implements a **defense-in-depth** security model for file uploads. Each layer provides independent protection, so a breach in one layer doesn't compromise the entire system.

┌─────────────────────────────────────────────────────────────┐
│ SECURITY LAYERS │
├─────────────────────────────────────────────────────────────┤
│ │
│ Layer 1: Browser Upload (Livewire) │
│ • Rate limiting per user │
│ • File size limits │
│ • Extension validation │
│ │
│ Layer 2: Server Validation │
│ • MIME magic-byte detection (finfo) │
│ • Image dimension validation (getimagesize) │
│ • Dangerous extension rejection │
│ • Path traversal prevention │
│ │
│ Layer 3: Authorization │
│ • Gate::authorize on all routes │
│ • Tenant isolation (org/project ownership) │
│ • TaskFileReference verification │
│ │
│ Layer 4: Storage │
│ • Private disk only │
│ • UUID-generated filenames │
│ • SHA-256 checksums │
│ • Duplicate detection │
│ │
│ Layer 5: Serving │
│ • Content-Disposition: attachment for dangerous types │
│ • Inline only for images/PDFs │
│ • Authorization on every route │
│ │
└─────────────────────────────────────────────────────────────┘

text

---

## File Upload Pipeline

The complete upload flow from user action to stored file:
User selects file
↓
Livewire validates file size/type (client-side)
↓
uploadAttachments() method called
↓
Rate limiting check (10/min per user)
↓
ValidateIncomingFileService::handle()
├── validateUpload() - Check file is valid
├── validateFilename() - Check filename length ≤ 255
├── validateDangerousExtensions() - Reject .php, .exe, etc.
├── validateMimeTypeAgainstContent() - finfo magic bytes
├── validateExtensionMatchesMimeType() - Extension ↔ MIME
├── validateImageContent() - getimagesize for images
└── validateRules() - Laravel validation rules
↓
UploadProjectAttachmentAction::handle()
├── Storage quota check (atomic)
├── Generate UUID filename
├── Store on private disk
├── SHA-256 checksum generation
├── Duplicate file detection
├── Create FileAttachment record
└── Activity logging
↓
File stored in storage/app/private/{org}/{project}/{uuid}.{ext}

text

---

## Storage Architecture

### Disk Configuration

```php
// config/filesystems.php

'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => false,  // NOT publicly accessible
],
Storage Paths
text
storage/app/private/
├── avatars/                    # User avatars (user-level, no org quota)
├── livewire-tmp/               # Temporary uploads (auto-cleaned)
├── projects/
│   └── {project-uuid}/
│       └── attachments/
│           └── {file-uuid}.{ext}
└── organizations/
    └── {org-uuid}/
        └── library/           # Future: org shared library
Key Design Decisions
UUID filenames - Original filename NEVER used for storage

Private disk only - No files in public directory

User avatars - Stored at avatars/ level, NOT under org

Project attachments - Stored under project UUID

Authorization Model
Route-Level Authorization
All file-serving routes require Gate::authorize('view', $project):

php
// routes/web.php
Route::get('/projects/{project}/attachments/{attachment}/download', function (...) {
    Gate::authorize('view', $project);
    // ... serve file
});
TaskFileReference Verification
Task attachments are project attachments referenced via TaskFileReference:

php
// Verify attachment belongs to same project as task
abort_unless(
    $attachment->attachable_type === Project::class
        && $attachment->attachable_id === $task->project_id
        && $task->fileReferences()
            ->where('file_attachment_id', $attachment->id)
            ->exists(),
    404,
);
AttachTaskResourceAction Authorization
php
// Verify attachment belongs to same project
if ($attachment->attachable_type !== Project::class
    || $attachment->attachable_id !== $task->project_id) {
    abort(404);
}

// Verify user can view the project
Gate::authorize('view', $task->project);
Validation Layers
Layer 1: Dangerous Extension Rejection
php
// app/Services/Storage/ValidateIncomingFileService.php
$blocked = [
    'php', 'php3', 'php4', 'php5', 'phtml',
    'exe', 'dll', 'bat', 'cmd', 'com',
    'jar', 'sh', 'ps1',
];
Layer 2: MIME Magic-Byte Detection
Uses PHP's finfo to detect ACTUAL file content, not browser-declared MIME:

php
$detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
Critical: We do NOT trust $file->getMimeType() (browser-supplied). A PHP file renamed to .jpg with Content-Type: image/jpeg will be detected as text/x-php by finfo.

Layer 3: Image Content Validation
Uses getimagesize() (available without GD extension) to verify:

Actual image dimensions

Image MIME type matches detected type

Dimensions within reasonable limits (max 10000x10000)

Layer 4: Extension ↔ MIME Match
text
Allowed combinations:
    image/jpeg  → jpg, jpeg
    image/png   → png
    image/webp  → webp
    image/gif   → gif
    application/pdf → pdf
    application/zip → zip
    text/plain  → txt, csv
    ... etc
Tenant Isolation
Cross-Tenant Attack Prevention
Attack	Defense	Test
Download other org's file	Gate::authorize('view', $project)	✅
Upload to other org's project	Route binding + authorization	✅
Delete other org's attachment	Gate::authorize('update', $project)	✅
Attach file to other org's task	Project ownership check	✅
Guess attachment ID	attachable_type + attachable_id match	✅
Organization Context
Files are ALWAYS associated with:

organization_id in StoredFile

attachable_type + attachable_id in FileAttachment

project_id in route parameters

Cross-tenant access requires bypassing all three layers.

Rate Limiting
Upload Rate Limits
Resource	Limit	Window
Uploads	10	1 minute
Deletions	20	1 minute
Login (Fortify)	5	1 minute
Two-factor	5	1 minute
Implementation
php
// app/Livewire/Comments/CommentSection.php
$rateLimitKey = 'upload:' . Auth::id();

if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
    $seconds = RateLimiter::availableIn($rateLimitKey);
    $this->addError('uploads', "Too many uploads. Wait {$seconds}s.");
    return;
}

RateLimiter::hit($rateLimitKey, 60);
Content-Disposition Security
Safe Inline Types (Preview in Browser)
MIME Type	Behavior	Risk
image/jpeg	Inline	Safe
image/png	Inline	Safe
image/gif	Inline	Safe
image/webp	Inline	Safe
application/pdf	Inline	Safe (sandboxed)
Force Download Types (Prevent XSS)
MIME Type	Behavior	Reason
text/html	Attachment	XSS risk
image/svg+xml	Attachment	XSS risk
application/xml	Attachment	XSS risk
Everything else	Attachment	Default safe
Key Principle
Any file that can execute JavaScript must NEVER be served inline from your domain.

When HTML/SVG files are served as attachments:

They download to user's machine

No access to your app's cookies/session

No ability to make authenticated API calls

Image Validation
What We Validate
MIME type - Using finfo magic bytes

Actual image content - Using getimagesize()

Dimensions - Width/height must be valid

Size limits - Max 10000x10000 pixels

Format consistency - finfo MIME must match getimagesize MIME

What We Reject
HTML disguised as JPEG (polyglot files)

Corrupted images

Decompression bombs (oversized dimensions)

Files with mismatched MIME types

What We Don't Validate (Future)
EXIF metadata (privacy concern)

Pixel-level manipulation (requires GD)

Animated GIF frame counts

Storage Quota Enforcement
How Quota Works
Organization plans define max_storage_mb

Usage tracking via OrganizationUsage model

Check before upload - Atomic transaction

Increase after upload - Via IncreaseStorageUsageAction

Decrease after delete - Via DecreaseStorageUsageAction

User Avatars Are Different
NOT counted against org quota

No org membership required

Only 2MB size limit (user-level)

Quota Enforcement Points
text
UploadStoredFileAction:
    ├── Check quota (atomic)
    ├── Store file
    └── Increase usage

UploadProjectAttachmentAction:
    ├── Check quota for attachment reference
    └── Create attachment record

DeleteFileAttachmentAction:
    ├── Delete physical file
    ├── Delete attachment record
    └── Decrease usage
Security Test Suite
Test Files
File	Tests	Coverage
tests/Feature/Security/UploadSecurityTest.php	5	Executable rejection, MIME spoofing, tenant isolation
tests/Feature/Security/StorageQuotaTest.php	3	Quota enforcement, avatar independence
tests/Feature/Security/ContentDispositionTest.php	3	XSS prevention via headers
tests/Feature/Security/ImageValidationTest.php	3	Image content verification
tests/Feature/Security/RateLimitTest.php	2	Abuse prevention
Test Data Setup
All security tests use:

php
beforeEach(function () {
    Storage::fake('private');
    [$this->org, $this->user] = $this->createOrganizationWithOwner();
    // ... create workspace, team, project
});
Running Tests
bash
# Run all security tests
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/

# Run specific test file
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/UploadSecurityTest.php
IMPORTANT: Always use SQLite in-memory for tests, not MySQL!

Attack Surface Map
Entry Points
Livewire CommentSection - uploadAttachments() method

Profile Settings - updateAvatar() method

Download Routes - projects.attachments.download

View Routes - projects.attachments.view

Task Attachment Routes - tasks.attachments.download/view

Trust Boundaries
text
┌─────────────────────────────────────────────┐
│           UNTRUSTED (User Input)           │
├─────────────────────────────────────────────┤
│  • Filename                                │
│  • File content                            │
│  • MIME type (browser-declared)           │
│  • File size                               │
│  • Attachment IDs (guessed)               │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│           VALIDATION LAYER                 │
├─────────────────────────────────────────────┤
│  • Extension check                         │
│  • MIME magic-byte detection              │
│  • Image dimension verification           │
│  • Filename sanitization                  │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│           AUTHORIZATION LAYER              │
├─────────────────────────────────────────────┤
│  • Gate::authorize('view', $project)      │
│  • Tenant isolation checks                │
│  • TaskFileReference verification         │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│           STORAGE LAYER                    │
├─────────────────────────────────────────────┤
│  • Private disk only                      │
│  • UUID-generated filenames               │
│  • SHA-256 checksums                      │
└─────────────────────────────────────────────┘
Known Vulnerabilities (Fixed)
Vulnerability 1: MIME Spoofing
Threat: Upload PHP shell with Content-Type: image/jpeg
Attack: curl -F "file=@shell.php;type=image/jpeg" /upload
Root Cause: Trusted browser-supplied MIME type
Fix: Added finfo magic-byte detection
Test: executable file with spoofed extension is rejected

Vulnerability 2: Cross-Tenant Download
Threat: User from Org B downloads Org A's attachment
Attack: GET /projects/{orgA-project}/attachments/{attachment}/download
Root Cause: Missing authorization check
Fix: Added Gate::authorize('view', $project)
Test: user from other tenant cannot download attachment

Vulnerability 3: Storage Quota Bypass
Threat: Duplicate file upload bypasses quota
Attack: Upload same file multiple times
Root Cause: Duplicate check returned before quota check
Fix: Reordered quota check before duplicate detection
Test: duplicate upload still counts against quota

Vulnerability 4: XSS via SVG/HTML
Threat: SVG with onload executes in victim's browser
Attack: Upload evil.svg with XSS payload
Root Cause: Served inline with Content-Type: image/svg+xml
Fix: Force Content-Disposition: attachment for dangerous types
Test: svg file is served as attachment not inline

Vulnerability 5: Avatar Required Organization
Threat: User without org can't upload avatar
Attack: New user tries to set profile photo
Root Cause: Avatar tied to org quota
Fix: Avatars are user-level, not org-level
Test: user without organization can still upload avatar

Deferred Security Work
Priority Queue
Priority	Item	Reason Deferred	When to Implement
HIGH	Zip bomb detection	ZIP uploads not yet enabled	When workspace→org library sharing is built
MEDIUM	EXIF stripping	Privacy enhancement	Before public file sharing
MEDIUM	SVG sanitization	SVG currently rejected	If users request SVG support
LOW	Antivirus scanning	Only needed for compliance	If enterprise clients require
LOW	Animated GIF validation	Low risk	If GIF uploads become common
Implementation Notes
Zip bomb detection (future):

php
// When ZIP uploads are enabled
$zip = new ZipArchive();
$zip->open($file->getRealPath());
// Check compression ratio > 100:1
// Check total uncompressed size
SVG sanitization (if needed):

bash
composer require enshrined/svg-sanitize
EXIF stripping (if needed):

bash
# Requires GD extension
docker-php-ext-install gd
Files Involved
Core Security Files
File	Purpose
app/Services/Storage/ValidateIncomingFileService.php	Main validation pipeline
app/Domain/Storage/Actions/UploadStoredFileAction.php	File storage + quota
app/Domain/Storage/Actions/UploadProjectAttachmentAction.php	Project attachment creation
app/Domain/Storage/Actions/DeleteFileAttachmentAction.php	Attachment deletion
app/Domain/Storage/Services/FileStorageService.php	Storage abstraction
app/Domain/Storage/Rules/FileUploadRules.php	Validation rules
app/Domain/Usage/Services/StorageQuotaService.php	Quota enforcement
app/Livewire/Comments/CommentSection.php	Upload UI + rate limiting
Route Files
File	Routes
routes/web.php	Download/view routes, avatar route
Test Files
File	Tests
tests/Feature/Security/UploadSecurityTest.php	Upload validation
tests/Feature/Security/StorageQuotaTest.php	Quota enforcement
tests/Feature/Security/ContentDispositionTest.php	Header security
tests/Feature/Security/ImageValidationTest.php	Image verification
tests/Feature/Security/RateLimitTest.php	Rate limiting
Testing Commands
Full Test Suite
bash
# All tests (MySQL - CAREFUL: clears DB!)
docker compose exec app php artisan test

# All tests (SQLite in-memory - SAFE)
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test
Security Tests Only
bash
# All security tests
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/

# Individual test files
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/UploadSecurityTest.php
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/StorageQuotaTest.php
Billing Tests
bash
./scripts/test-billing.sh
Key Architectural Decisions
1. Private Disk Only
Decision: All uploads go to storage/app/private/
Rationale: Files must never be publicly accessible without authorization
Consequence: All file access requires application-level authorization

2. UUID Filenames
Decision: Server generates UUID for storage filename
Rationale: Original filenames can contain path traversal, XSS payloads
Consequence: Original filename stored only as metadata

3. User Avatars Are User-Level
Decision: Avatars not tied to organization quota
Rationale: Users can exist without organizations
Consequence: Avatar storage is separate from org storage

4. Content-Disposition: Attachment for Dangerous Types
Decision: Force download for HTML/SVG/XML
Rationale: Prevents stored XSS via file upload
Consequence: Slight UX inconvenience (no inline preview for these types)

5. Rate Limiting at Application Level
Decision: Rate limit in Livewire component (not middleware)
Rationale: More granular control, user-specific limits
Consequence: 10 uploads/min per user, 20 deletions/min per user

Common Issues & Fixes
Issue: "File content MIME type (text/plain) does not match declared MIME type (application/pdf)"
Cause: File content doesn't match extension/MIME
Fix: Upload actual PDF file, or use createWithContent() with %PDF-1.4 header in tests

Issue: "The organization has exceeded its storage quota"
Cause: Storage quota exceeded
Fix: Upgrade plan, delete old files, or increase max_storage_mb in plan

Issue: Tests using MySQL instead of SQLite
Cause: Running php artisan test without environment override
Fix: Always use -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory:

Issue: Component missing error: file
Cause: Wrong error key in test assertion
Fix: Check if error is on file or uploads key (differs by validation layer)

Version History
Date	Change	Author
2026-09-01	Initial security documentation	Security audit
2026-09-01	Added MIME magic-byte validation	Security audit
2026-09-01	Added Content-Disposition security	Security audit
2026-09-01	Added rate limiting	Security audit
2026-09-01	Added image validation	Security audit
2026-09-01	Added storage quota enforcement	Security audit
This document serves as the definitive reference for TaskForge's upload security architecture. Update it whenever security features are added or modified.
