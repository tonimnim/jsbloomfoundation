#!/bin/bash

# Deploy commands for Laravel Cloud
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan migrate --force
php artisan storage:link || true
php artisan storage:ensure-link || true