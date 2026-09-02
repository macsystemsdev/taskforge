#!/bin/bash

echo "🧪 Running Billing Tests (SQLite - Safe)"
echo "========================================"

# Use SQLite in-memory to prevent MySQL data loss
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test \
    tests/Feature/Billing/ \
    tests/Unit/Billing/ \
    --compact

echo ""
echo "✅ Billing tests complete"
