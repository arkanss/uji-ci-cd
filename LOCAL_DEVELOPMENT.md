# Local Docker Development Guide

This guide helps you build and test the Docker container locally before deploying to the server.

## Prerequisites

- Docker installed on your machine
- Docker Compose (usually comes with Docker Desktop)
- A local PostgreSQL database (optional - can use host machine's database)

## Quick Start

### 1. Configure Environment

Edit `.env.local` file and update the following values:

```bash
# Generate a new app key
php artisan key:generate --show

# Update .env.local with the generated key
APP_KEY=base64:your-generated-key-here

# Database configuration (if using host machine's database)
DB_HOST=host.docker.internal
DB_DATABASE=your_local_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 2. Build and Run with Docker Compose

```bash
# Build and start the container
docker-compose -f docker-compose.local.yml up --build

# Or run in detached mode (background)
docker-compose -f docker-compose.local.yml up --build -d

# View logs
docker-compose -f docker-compose.local.yml logs -f

# Stop the container
docker-compose -f docker-compose.local.yml down
```

### 3. Access the Application

Open your browser and navigate to:
- **Application**: http://localhost:8080
- **Health Check**: http://localhost:8080/health

## Manual Docker Commands

If you prefer to use Docker directly without Docker Compose:

```bash
# 1. Build the image
docker build -t localplace-cms:local .

# 2. Run the container
docker run -d \
  --name localplace-cms-local \
  -p 8080:8080 \
  --env-file .env.local \
  --add-host host.docker.internal:host-gateway \
  localplace-cms:local

# 3. View logs
docker logs -f localplace-cms-local

# 4. Stop and remove
docker stop localplace-cms-local
docker rm localplace-cms-local
```

## Debugging

### Check Built Assets

```bash
# Check if Livewire assets exist
docker exec localplace-cms-local ls -la /var/www/html/public/vendor/livewire/

# Check if Vite build assets exist
docker exec localplace-cms-local ls -la /var/www/html/public/build/assets/
```

### Access Container Shell

```bash
# Enter the container
docker exec -it localplace-cms-local sh

# Once inside, you can:
cd /var/www/html
php artisan route:list
php artisan config:cache
```

### Test Routes from Inside Container

```bash
# Test Livewire route
docker exec localplace-cms-local curl -i http://localhost:8080/livewire/livewire.js

# Test Flux route
docker exec localplace-cms-local curl -i http://localhost:8080/flux/flux.js

# Test health endpoint
docker exec localplace-cms-local curl -i http://localhost:8080/health
```

### View Nginx Logs

```bash
# Access logs
docker exec localplace-cms-local tail -f /var/log/nginx/access.log

# Error logs
docker exec localplace-cms-local tail -f /var/log/nginx/error.log
```

### View Laravel Logs

```bash
# View latest Laravel logs
docker exec localplace-cms-local tail -f /var/www/html/storage/logs/laravel.log
```

## Database Setup

### Using Host Machine Database

The container is configured to access your host machine's database using `host.docker.internal`.

Make sure your PostgreSQL is running and accessible:

```bash
# On macOS/Linux, ensure PostgreSQL is listening on all interfaces
# Edit postgresql.conf:
listen_addresses = '*'

# Edit pg_hba.conf to allow Docker container connections:
host    all             all             172.17.0.0/16           md5
```

### Run Migrations

```bash
# Run migrations
docker exec localplace-cms-local php artisan migrate

# Or with fresh database
docker exec localplace-cms-local php artisan migrate:fresh --seed
```

## Troubleshooting

### Assets Not Loading

1. Check if assets were built:
   ```bash
   docker exec localplace-cms-local ls -la /var/www/html/public/build/
   ```

2. Clear browser cache completely

3. Check nginx configuration:
   ```bash
   docker exec localplace-cms-local cat /etc/nginx/http.d/default.conf
   ```

### Database Connection Issues

1. Test connection from container:
   ```bash
   docker exec localplace-cms-local php artisan tinker
   # Then run: DB::connection()->getPdo();
   ```

2. Verify host.docker.internal resolves:
   ```bash
   docker exec localplace-cms-local ping -c 3 host.docker.internal
   ```

### Permission Issues

If you encounter permission issues with storage:

```bash
docker exec localplace-cms-local chmod -R 777 /var/www/html/storage
docker exec localplace-cms-local chmod -R 777 /var/www/html/bootstrap/cache
```

## Clean Up

```bash
# Stop and remove container
docker-compose -f docker-compose.local.yml down

# Remove container and volumes
docker-compose -f docker-compose.local.yml down -v

# Remove the built image
docker rmi localplace-cms:local
```

## Next Steps

Once you've verified everything works locally:

1. Commit your changes
2. Push to the repository
3. Build and deploy to staging/production using the CI/CD pipeline
4. Remember to purge Cloudflare cache after deployment
