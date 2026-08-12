# Week 4 — Docker Containerization & Development Environment

## Day 1 — Building the TaskForge Docker Image

### Objective

Begin containerizing TaskForge and build a production-oriented Docker image capable of running the Laravel application and its required services.

### Work Completed

- Started learning and implementing Docker containerization for TaskForge.
- Created a multi-stage Dockerfile.
- Used a PHP 8.4 CLI base image.
- Installed required system dependencies.
- Installed required PHP extensions:
  - `bcmath`
  - `intl`
  - `mbstring`
  - `pcntl`
  - `pdo_mysql`
  - `xml`
  - `zip`
- Installed and enabled the Redis PHP extension.
- Added a dedicated Composer dependency stage.
- Added a Node.js frontend build stage.
- Built production assets using Vite.
- Created the final Laravel runtime image.

### Problems Encountered

Composer failed while installing production dependencies because Laravel Horizon requires the `pcntl` PHP extension.

The error identified:

```text
laravel/horizon requires ext-pcntl
Solution

Added pcntl to the PHP extensions installed in the Docker image.

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    pcntl \
    pdo_mysql \
    xml \
    zip
Result

The Docker image successfully built after the required runtime extension was added.

Key Learning

The Docker environment must independently satisfy the platform requirements of the application's dependencies.

A dependency that works on the local machine does not automatically work inside a clean Linux container.

Day 2 — Debugging the Container Runtime
Objective

Run TaskForge as multiple Docker services and resolve application startup failures.

Work Completed

Created Docker Compose services for:

app
horizon
scheduler
redis

The Laravel application image was reused by the application, Horizon, and Scheduler services.

taskforge:latest
        │
        ├── php artisan serve
        ├── php artisan horizon
        └── php artisan schedule:work
Problems Encountered

The application, Horizon, and Scheduler entered restart loops with:

Class "Laravel\Boost\BoostServiceProvider" not found

Investigation included checking:

bootstrap/providers.php
bootstrap/cache/packages.php
Installed Composer packages inside the container

The issue was caused by package discovery metadata referencing packages that were not available in the production dependency set.

Another issue occurred when attempting to run:

php artisan package:discover --ansi

during the image build.

Laravel bootstrapping failed because application configuration required runtime environment values that were not available during the Docker build.

Solution

Corrected the package discovery/cache mismatch and separated image construction from environment-dependent application bootstrapping.

The Docker image should package the application and its dependencies.

Runtime-dependent application initialization should not be blindly forced during image construction.

Result

The application image was successfully built and the Laravel services could proceed toward runtime testing.

Key Learning

Docker has distinct execution phases:

Build Time
    ↓
Creates the image

Runtime
    ↓
Runs the application

The assumptions available during application runtime do not automatically exist during image construction.

Day 3 — Docker Volumes, WSL 2 Migration & Runtime Stabilization
Objective

Improve the Docker development workflow, avoid unnecessary image rebuilds, and eliminate Windows filesystem performance overhead.

Initial Observation

TaskForge was noticeably faster when running directly from the Docker/Linux filesystem.

Requests that were previously significantly slower on the Windows development setup became much faster.

The main performance issue was the filesystem boundary between:

Windows NTFS
    ↓
WSL 2 / Docker filesystem translation
    ↓
Linux container

Laravel loads and reads many files during normal operation, making the cross-filesystem overhead noticeable.

Development Volume Setup

A bind mount was added to avoid rebuilding the Docker image after every application code change.

volumes:
  - .:/var/www/html

Redis persistence was also configured using a named volume.

redis:
  volumes:
    - redis-data:/data

volumes:
  redis-data:
Problem — Application Restart Loop

After adding the bind mount, the Laravel containers entered a restart loop.

The application failed with:

Failed opening required '/var/www/html/vendor/autoload.php'
Cause

The bind mount:

- .:/var/www/html

replaced the application directory that had been packaged inside the Docker image.

The new project directory did not contain the vendor directory.

As a result, the bind mount hid the image's existing Composer dependencies.

Solution

Installed Composer dependencies into the mounted project before starting the services.

docker compose run --rm app composer install
docker compose up -d

The application, Horizon, and Scheduler then started successfully.

WSL 2 Migration

The project was moved from the Windows filesystem into the Ubuntu WSL filesystem.

The preferred development location became:

~/code/taskforge

rather than:

/mnt/d/Code/taskforge

The outstanding local changes were committed and pushed before migration.

The repository was then cloned into WSL.

WSL Environment Verification

Verified Linux-native installations of:

git --version
docker --version
docker compose version
Node.js Problem

WSL initially attempted to use the Windows installation of Node.js.

This caused path and UNC-related failures because Windows executables were receiving Linux filesystem paths.

Solution

Installed Node.js natively inside Ubuntu using NVM.

nvm install --lts

The existing dependencies were then recreated inside WSL.

rm -rf node_modules
npm install

The frontend build then completed successfully.

Redis Networking Problem

Laravel initially attempted to connect to Redis through:

127.0.0.1:6379

This failed because 127.0.0.1 inside a container refers to that specific container.

Redis was running in a separate container.

Solution

Configured Redis using the Docker Compose service name.

REDIS_HOST=redis

Docker Compose resolves service names through its internal network.

Notification Issues

A notification failure occurred because of incorrect relationship traversal.

The attempted logic assumed a relationship could be traversed through a model chain that did not actually exist.

The relationship was corrected using the valid TaskForge domain path.

A separate notification issue involved environment-specific URLs.

Notification records contained URLs pointing to:

http://taskforge.test

while the Docker application was running on:

http://localhost:8000
Solution

Updated the local application URL.

APP_URL=http://localhost:8000

The notification redirect logic was also adjusted to work with relative paths rather than environment-specific absolute URLs.

Future notification payloads should prefer relative routes where appropriate.

route('route.name', [], false)
Result

TaskForge is now running successfully inside Docker from the WSL Linux filesystem.

The current architecture is:

Windows
    │
    ▼
WSL 2 / Ubuntu
    │
    ▼
TaskForge source code
    │
    ▼
Docker Compose
    │
    ├── app
    ├── horizon
    ├── scheduler
    └── redis
Key Learnings
A bind mount can hide files already packaged inside a Docker image.
127.0.0.1 inside Docker refers to the current container.
Docker service names should be used for communication between Compose services.
Linux-native development tools should be used when working inside WSL.
Persistent application data should avoid environment-specific absolute URLs.
Keeping the project inside the WSL Linux filesystem provides a development environment closer to the eventual production runtime.