# Ustawi Wa Jamii - Docker Development Environment
.PHONY: help up down restart build logs ps clean migrate seed fresh install dev build-assets test

# Default command
help:
	@echo "Ustawi Wa Jamii - Docker Development Commands"
	@echo "============================================="
	@echo "make up              - Start all Docker services"
	@echo "make down            - Stop all Docker services"
	@echo "make restart         - Restart all Docker services"
	@echo "make build           - Build Docker images"
	@echo "make logs            - View logs from all services"
	@echo "make ps              - List running containers"
	@echo "make clean           - Clean up Docker volumes (WARNING: Deletes data)"
	@echo ""
	@echo "Database Commands:"
	@echo "make db-shell        - Access PostgreSQL shell"
	@echo "make migrate         - Run database migrations"
	@echo "make seed            - Seed the database"
	@echo "make fresh           - Fresh migration with seeders"
	@echo ""
	@echo "Application Commands:"
	@echo "make install         - Install dependencies (Composer & NPM)"
	@echo "make dev             - Start Laravel development server"
	@echo "make build-assets    - Build frontend assets"
	@echo "make test            - Run tests"
	@echo "make tinker          - Open Laravel Tinker"
	@echo "make clear           - Clear all Laravel caches"

# Docker commands
up:
	docker-compose up -d
	@echo "Services started! Access:"
	@echo "- Application: http://localhost:8000"
	@echo "- Mailhog: http://localhost:8025"
	@echo "- MinIO: http://localhost:9001"

down:
	docker-compose down

restart:
	docker-compose restart

build:
	docker-compose build --no-cache

logs:
	docker-compose logs -f

ps:
	docker-compose ps

clean:
	@echo "WARNING: This will delete all data!"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	docker-compose down -v
	rm -rf storage/app/public/*
	rm -rf storage/logs/*

# Database commands
db-shell:
	docker-compose exec postgres psql -U ustawi_user -d ustawi

migrate:
	php artisan migrate

seed:
	php artisan db:seed

fresh:
	php artisan migrate:fresh --seed

# Application commands
install:
	composer install
	npm install
	cp .env.example .env
	php artisan key:generate
	php artisan storage:link

dev:
	npm run dev & php artisan serve

build-assets:
	npm run build

test:
	php artisan test

tinker:
	php artisan tinker

clear:
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	php artisan optimize:clear

# Quick setup for new developers
setup: install up migrate seed
	@echo "Setup complete! Visit http://localhost:8000"