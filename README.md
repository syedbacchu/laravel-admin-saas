# laravel-admin-saas
Enterprise-grade SaaS foundation built in Laravel with landlord/tenant architecture, dynamic database switching, automated tenant provisioning, and scalable multi-database infrastructure.

## Docker Setup

### 1) Prepare environment

```bash
cp .env.docker.example .env
```

### 2) Build and start containers

```bash
docker compose up -d --build
```

### 3) Install dependencies

```bash
docker compose exec app composer install
docker compose exec node npm install
```

### 4) Generate app key

```bash
docker compose exec app php artisan key:generate
```

### 5) Run migrations and seeders

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

### 6) Frontend assets

```bash
# one-time build
docker compose exec node npm run build

# or dev mode
docker compose exec node npm run dev -- --host
```

### 7) Access services

- Laravel app: http://localhost:8080
- phpMyAdmin: http://localhost:8081
  - Server: `mysql`
  - Username: `root`
  - Password: `root`

### Useful commands

```bash
# Stop containers
docker compose down

# Stop and remove DB volume (fresh database)
docker compose down -v

# Run tenant migrations for all tenant DBs
docker compose exec app php artisan tenant:migrate

# Run tenant migrations for specific tenant
docker compose exec app php artisan tenant:migrate --company_username=rifatmotor
```
