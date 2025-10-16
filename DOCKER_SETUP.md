# Docker Development Environment Setup

This guide will help you set up the Ustawi Wa Jamii application using Docker for local development with PostgreSQL.

## Prerequisites

- Docker and Docker Compose installed on your machine
- PHP 8.2+ (for running artisan commands)
- Composer (for PHP dependencies)
- Node.js 18+ and NPM (for frontend assets)

## Quick Start

1. **Clone the repository and navigate to the project directory**
   ```bash
   cd ustawi
   ```

2. **Copy the environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start Docker services**
   ```bash
   docker-compose up -d
   ```
   
   This will start:
   - PostgreSQL database (port 5432)
   - Redis cache server (port 6379)
   - Mailhog email testing (port 8025 for UI, 1025 for SMTP)
   - MinIO S3-compatible storage (port 9001 for UI, 9000 for API)

4. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database (optional)**
   ```bash
   php artisan db:seed
   ```

8. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

9. **Build frontend assets**
   ```bash
   npm run build
   ```

10. **Start the development server**
    ```bash
    npm run dev
    ```
    
    In a separate terminal:
    ```bash
    php artisan serve
    ```

## Using Make Commands

We've included a Makefile for convenience. Here are the available commands:

```bash
# Start all services
make up

# Stop all services
make down

# Install dependencies and setup
make setup

# Run migrations
make migrate

# Fresh migration with seeders
make fresh

# View logs
make logs

# Access PostgreSQL shell
make db-shell
```

## Accessing Services

Once everything is running, you can access:

- **Application**: http://localhost:8000
- **Mailhog UI**: http://localhost:8025 (view sent emails)
- **MinIO Console**: http://localhost:9001 (login: minioadmin/minioadmin)

## Database Connection

The application is configured to use PostgreSQL. Default connection details:

- **Host**: 127.0.0.1 (or localhost)
- **Port**: 5432
- **Database**: ustawi
- **Username**: ustawi_user
- **Password**: ustawi_password

## Troubleshooting

### Port Conflicts

If you have port conflicts, you can change the ports in `docker-compose.yml`:

```yaml
services:
  postgres:
    ports:
      - "5433:5432"  # Change 5433 to any available port
```

Then update your `.env` file:
```
DB_PORT=5433
```

### Database Connection Issues

If you can't connect to the database:

1. Check if the container is running:
   ```bash
   docker-compose ps
   ```

2. Check the logs:
   ```bash
   docker-compose logs postgres
   ```

3. Ensure the database is ready:
   ```bash
   docker-compose exec postgres pg_isready -U ustawi_user -d ustawi
   ```

### Permission Issues

If you encounter permission issues with storage:

```bash
chmod -R 775 storage bootstrap/cache
```

## Stopping the Environment

To stop all Docker services:
```bash
docker-compose down
```

To stop and remove all data (WARNING: This will delete your database):
```bash
docker-compose down -v
```

## Production Considerations

This Docker setup is for **local development only**. For production:

1. Use managed PostgreSQL service
2. Use proper Redis configuration with authentication
3. Use actual email service (not Mailhog)
4. Use proper S3 or cloud storage (not MinIO)
5. Set appropriate environment variables
6. Enable SSL/TLS
7. Configure proper backup strategies