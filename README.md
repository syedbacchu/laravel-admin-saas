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

## Tenant Database Commands

Use these commands for tenant database schema management.

```bash
# Run tenant migrations for all tenant databases
docker compose exec app php artisan tenant:migrate

# Run tenant migrations for one tenant by ID
docker compose exec app php artisan tenant:migrate --tenant_id=1

# Run tenant migrations for one tenant by company username
docker compose exec app php artisan tenant:migrate --company_username=rifatmotor

# Drop all tables and rebuild tenant schema for all tenants
docker compose exec app php artisan tenant:migrate:fresh

# Drop all tables and rebuild tenant schema for one tenant
docker compose exec app php artisan tenant:migrate:fresh --tenant_id=1
docker compose exec app php artisan tenant:migrate:fresh --company_username=rifatmotor
```

Notes:

- `tenant:migrate` runs only pending tenant migrations.
- `tenant:migrate:fresh` is destructive. It drops all existing tables in the tenant database and recreates them from `database/migrations/tenant`.
- Use `tenant:migrate:fresh` only when you want a fully clean tenant database.

## Common Api
- Test Connection
- Blogs
- basic home with pricing
- profil
- vehicle category
- registration serials
- registration zone
- areas

## Tenant Api
- Auth: login , password reset
- Profile, update , change password
- staff
- dashboard
- file system
- customers
- offices
- daily office expense
- salary expense
- employees
