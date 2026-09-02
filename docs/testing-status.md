# Testing Status — Updated 2026-09-01

## Current: 136 passed, 8 skipped (279 assertions)

## Test Suites

| Suite | Tests | Status |
|-------|-------|--------|
| Security | 19 | ✅ All pass |
| Billing | 62 | ✅ Pass (1 float fixed) |
| Auth | 12 | ✅ Pass (3 skipped: 2FA) |
| Teams | 10 | ✅ Pass (2 skipped: org setup) |
| Settings | 9 | ✅ Pass |
| Unit | 4 | ✅ Pass (RefreshDatabase added) |

## Skipped Tests (8)

| Reason | Count | Fix When |
|--------|-------|----------|
| 2FA not configured | 2 | Enable Fortify 2FA |
| Notification queue | 3 | Fix queue mocking |
| Team org setup | 2 | Update test data |
| Password reset queue | 1 | Fix queue mocking |

## Test Commands

