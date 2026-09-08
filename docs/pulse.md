I'll provide the complete `docs/pulse-setup.md` file content below. Copy everything inside the code block and save it as `docs/pulse-setup.md`.

```markdown
# Laravel Pulse Setup & Troubleshooting

This guide covers installing Pulse in TaskForge and the fixes required for MySQL 9.7 and Redis/Predis compatibility.

## 1. Installation

```bash
docker compose exec app composer require laravel/pulse
docker compose exec app php artisan pulse:install
docker compose exec app php artisan migrate
```

Publish the dashboard if you plan to customize cards:

```bash
docker compose exec app php artisan vendor:publish --tag=pulse-dashboard
```

## 2. Environment Configuration

Add to `.env`:

```dotenv
PULSE_ENABLED=true
PULSE_PATH=pulse
```

## 3. Authorization

Restrict Pulse to the platform owner in `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewPulse', function (\App\Models\User $user) {
    $adminEmail = config('services.filament.admin_email');

    return ! empty($adminEmail)
        && $user->email === $adminEmail
        && $user->hasVerifiedEmail();
});
```

## 4. MySQL 9.7 Fix

MySQL 9.7 disallows `md5()` in generated columns. The default Pulse migration uses:

```php
$table->char('key_hash', 16)->charset('binary')->virtualAs('unhex(md5(`key`))')
```

This fails with:

```text
SQLSTATE[HY000]: General error: 3763 Expression of generated column 'key_hash' contains a disallowed function: `md5`.
```

### Fix

Change the MySQL/MariaDB branch in the Pulse migration to a regular application-managed column:

```php
'mariadb', 'mysql' => $table->string('key_hash', 32)->nullable(),
```

Pulse already computes `md5($attributes['key'])` in `DatabaseStorage`, so database-level generation is unnecessary.

## 5. Redis Client & Serializer Fix

Pulse caches PHP objects such as `Illuminate\Support\Collection`. With `predis`, those objects can come back as `__PHP_Incomplete_Class`, causing:

```text
The script tried to call a method on an incomplete object.
```

### Fix

Use the **phpredis** client and explicitly set the PHP serializer.

Set in `.env`:

```dotenv
REDIS_CLIENT=phpredis
```

Add to `config/database.php` inside the `redis.options` array:

```php
'serializer' => \Redis::SERIALIZER_PHP, // use 1 if avoiding class constant
```

Example:

```php
'options' => [
    'cluster' => env('REDIS_CLUSTER', 'redis'),
    'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')) . '-database-'),
    'persistent' => env('REDIS_PERSISTENT', false),
    'serializer' => 1, // Redis::SERIALIZER_PHP
],
```

After changing Redis env vars, recreate the container:

```bash
docker compose up -d --force-recreate app
```

Then flush Redis cache and Pulse data:

```bash
docker compose exec app php artisan tinker --execute="
Illuminate\Support\Facades\Redis::connection('cache')->flushdb();
DB::table('pulse_values')->truncate();
DB::table('pulse_entries')->truncate();
DB::table('pulse_aggregates')->truncate();
echo 'Redis cache + Pulse tables cleared';
"

docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
```

## 6. CI-Specific Notes

- Create required storage directories before running artisan commands:

```yaml
- name: Create Storage Directories
  run: |
    mkdir -p storage/framework/views
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/cache/data
```

- Use `.env.testing` with SQLite/array/sync for tests:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
CACHE_STORE=array
```

- Generate application key properly and export it:

```yaml
- name: Generate Application Key
  run: |
    KEY=$(php artisan key:generate --show)

    if grep -q '^APP_KEY=' .env; then
      sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env
    else
      echo "APP_KEY=${KEY}" >> .env
    fi

    echo "APP_KEY=${KEY}" >> $GITHUB_ENV
    php artisan config:clear
```

## 7. Quick Health Checks

```bash
docker compose exec app php artisan pulse:check
docker compose exec app php artisan tinker --execute="
echo 'Client config: ' . config('database.redis.client') . PHP_EOL;
echo 'Cache client: ' . Illuminate\Support\Facades\Redis::connection('cache')->client()::class . PHP_EOL;
"
```

Expected Redis client should be `PhpRedis` / `Redis`, not `Predis\Client`.

## 8. Common Issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| `JsonException` in Exceptions card | Malformed JSON in `pulse_entries` / `pulse_aggregates` | Truncate Pulse tables; re-enable Exceptions recorder carefully |
| `__PHP_Incomplete_Class` / incomplete object | Predis serialization problem | Switch to phpredis + Redis PHP serializer |
| `Please provide a valid cache path` | Missing `storage/framework/views` | Create storage directories |
| Missing APP_KEY | Key not set before tests | Generate/export APP_KEY in CI |
| Pulse cards empty after install | No traffic yet | Interact with app; cards populate over time |

## 9. Seeding Demo Data

After installing Pulse, refresh demo data:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

This now includes realistic organizations, plans, subscriptions, usage, and payment transactions.
```

You can save this as `docs/pulse-setup.md` in your repository. After saving, run:

```bash
git add docs/pulse-setup.md
git commit -m "docs: document Pulse setup and production fixes"
git push origin develop
```