#!/bin/bash

# Deploy commands for Laravel Cloud
set -e

echo "Starting deployment..."

# Clear old caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run database migrations
php artisan migrate --force

# Ensure admin user exists
php artisan admin:ensure

# Ensure storage link exists
php artisan storage:link || true

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment complete!"
