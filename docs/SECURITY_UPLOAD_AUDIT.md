# Upload Security Audit — Complete (2026-09-01)

## What Was Secured

| Vulnerability | Fix | Test File |
|--------------|-----|-----------|
| MIME spoofing (PHP as .jpg) | `finfo` magic-byte detection | UploadSecurityTest |
| Executable upload (.php, .exe) | Extension blocklist + MIME check | UploadSecurityTest |
| Cross-tenant access | `Gate::authorize` + ownership checks | UploadSecurityTest |
| Storage quota bypass | Atomic check before file storage | StorageQuotaTest |
| XSS via SVG/HTML preview | `Content-Disposition: attachment` | ContentDispositionTest |
| Corrupted image upload | `getimagesize()` validation | ImageValidationTest |
| Upload flooding | Rate limiting (10/min/user) | RateLimitTest |
| Zip bombs | Compression ratio + file count limits | ZipBombDetectionTest |

## Key Architecture Decisions

1. **Avatars are user-level** — No org required, no quota. `updateAvatar()` uses direct `store()`.

2. **Private disk only** — All files in `storage/app/private/`. No public URL access.

3. **UUID filenames** — Original filename stored as metadata only. Prevents path traversal.

4. **Validation pipeline order** — Size → Extension → MIME → Image → EXIF → ZIP.

5. **Error keys** — Livewire components add errors to `uploads` (property name), not `file` (validation key).

## Critical Files

- `app/Services/Storage/ValidateIncomingFileService.php` — All validation logic
- `app/Domain/Storage/Actions/UploadStoredFileAction.php` — Storage + quota
- `app/Livewire/Comments/CommentSection.php` — Upload UI + rate limiting
- `routes/web.php` — Serving routes (lines 120-230)

## Deferred Work (When Needed)

| Item | Blocked By | Action |
|------|-----------|--------|
| ZIP extraction security | Feature not built | TODO comments in ValidateIncomingFileService |
| EXIF stripping | GD extension missing | Dockerfile has `exif`, add `gd` |
| SVG sanitization | SVG rejected | Use `enshrined/svg-sanitize` if needed |

## Test Commands

```bash
# Security tests only (safe — SQLite)
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test tests/Feature/Security/

# Full suite
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test
Test Data Setup
Security tests use createOrganizationWithOwner() helper. Creates:

Org with owner user

Workspace (slug, description, is_default)

Team (is_personal, created_by)

Project (status active)

Gotchas
Unit tests need RefreshDatabase — Added to PaymentAmountTest and SubscriptionTest.

Float comparison — Cast both sides to (float) for decimal:2 fields.

Error assertions — Use assertHasErrors(['uploads']), not ['file'].

Test ZIPs — Use UploadedFile::fake()->createWithContent() not new UploadedFile().

SQLite tests — Always use -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: to avoid wiping MySQL.
